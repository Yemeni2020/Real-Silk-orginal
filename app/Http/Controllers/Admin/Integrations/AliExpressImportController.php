<?php

namespace App\Http\Controllers\Admin\Integrations;

use App\Http\Controllers\Controller;
use App\Jobs\AliExpressImportPublishJob;
use App\Models\AliExpressImportQueue;
use App\Models\AliExpressProduct;
use App\Models\AliExpressProductPreview;
use App\Models\Category;
use App\Services\AliExpress\AliExpressProductImporter;
use App\Services\AliExpress\AliExpressProductPreviewService;
use App\Services\AliExpress\AliExpressProductPolicy;
use App\Services\AliExpress\AliExpressStoreProductPublisher;
use App\Services\AliExpress\AliExpressTokenStore;
use App\Services\IntegrationLogService;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\View\View;
use RuntimeException;

class AliExpressImportController extends Controller
{
    public function index(Request $request, AliExpressTokenStore $tokenStore): View
    {
        $categories = Category::query()
            ->where('parent_id', 0)
            ->select(['id', 'name'])
            ->orderBy('name')
            ->get();

        if (Schema::hasTable('aliexpress_import_queues')) {
            $queueItems = AliExpressImportQueue::query()
                ->latest('id')
                ->paginate(20);
            $queueCounts = AliExpressImportQueue::query()
                ->selectRaw('status, count(*) as total')
                ->groupBy('status')
                ->pluck('total', 'status')
                ->all();
            $batches = AliExpressImportQueue::query()
                ->whereNotNull('batch_id')
                ->selectRaw("batch_id, count(*) as total, sum(case when status in ('success', 'completed') then 1 else 0 end) as completed, max(error_message) as last_error")
                ->groupBy('batch_id')
                ->orderByRaw('max(id) desc')
                ->limit(10)
                ->get();
        } else {
            $queueItems = new LengthAwarePaginator(
                collect([]),
                0,
                20,
                LengthAwarePaginator::resolveCurrentPage(),
                [
                    'path' => LengthAwarePaginator::resolveCurrentPath(),
                    'pageName' => 'page',
                ]
            );
            Toastr::warning('AliExpress queue table is missing. Please run: php artisan migrate');
            $queueCounts = [];
            $batches = collect([]);
        }

        $connection = $tokenStore->getConnectionStatus();

        return view('admin-views.integrations.aliexpress.index', compact('categories', 'queueItems', 'queueCounts', 'batches', 'connection'));
    }

    public function reconnect(): RedirectResponse
    {
        return redirect()->route('integrations.aliexpress.connect');
    }

    public function previewForm(): View
    {
        $categories = Category::query()->where('parent_id', 0)->select(['id', 'name'])->orderBy('name')->get();
        $recentPreviews = Schema::hasTable('aliexpress_product_previews')
            ? AliExpressProductPreview::query()->latest('id')->limit(10)->get()
            : collect([]);

        return view('admin-views.integrations.aliexpress.preview', [
            'categories' => $categories,
            'recentPreviews' => $recentPreviews,
            'preview' => null,
            'policyResult' => ['blocked' => false, 'warnings' => [], 'block_reasons' => []],
            'selectedCategoryId' => null,
        ]);
    }

    public function createPreview(Request $request, AliExpressProductPreviewService $previewService): View|RedirectResponse
    {
        $validated = $request->validate([
            'product_input' => 'required|string|max:500',
            'category_id' => 'nullable|integer|exists:categories,id',
        ]);

        try {
            $preview = $previewService->createPreview(
                $validated['product_input'],
                isset($validated['category_id']) ? (int) $validated['category_id'] : null,
                auth('admin')->id()
            );
            $categories = Category::query()->where('parent_id', 0)->select(['id', 'name'])->orderBy('name')->get();
            $policyResult = [
                'blocked' => $preview->policy_status === 'blocked',
                'warnings' => $preview->warnings ?? [],
                'block_reasons' => $preview->block_reasons ?? [],
            ];

            return view('admin-views.integrations.aliexpress.preview', [
                'preview' => $preview,
                'policyResult' => $policyResult,
                'categories' => $categories,
                'recentPreviews' => collect([]),
                'selectedCategoryId' => $validated['category_id'] ?? null,
            ]);
        } catch (\Throwable $exception) {
            Toastr::error($exception->getMessage());
            return redirect()->route('admin.aliexpress.preview');
        }
    }

    public function showPreview(int $id): View|RedirectResponse
    {
        $preview = AliExpressProductPreview::query()->find($id);
        if (!$preview) {
            Toastr::error('AliExpress preview not found.');
            return redirect()->route('admin.aliexpress.preview');
        }

        $categories = Category::query()->where('parent_id', 0)->select(['id', 'name'])->orderBy('name')->get();

        return view('admin-views.integrations.aliexpress.preview', [
            'preview' => $preview,
            'policyResult' => [
                'blocked' => $preview->policy_status === 'blocked',
                'warnings' => $preview->warnings ?? [],
                'block_reasons' => $preview->block_reasons ?? [],
            ],
            'categories' => $categories,
            'recentPreviews' => collect([]),
            'selectedCategoryId' => $preview->category_id,
        ]);
    }

    public function importPreview(Request $request, AliExpressProductPreviewService $previewService): RedirectResponse
    {
        $validated = $request->validate(['preview_id' => 'required|integer|exists:aliexpress_product_previews,id']);

        try {
            $preview = AliExpressProductPreview::query()->findOrFail($validated['preview_id']);
            $product = $previewService->importFromPreview($preview, auth('admin')->id());
            Toastr::success('AliExpress product imported from preview: ' . $product->ali_express_product_id);
            return redirect()->route('admin.aliexpress.preview.show', [$preview->id]);
        } catch (\Throwable $exception) {
            Toastr::error($exception->getMessage());
            return back();
        }
    }

    public function publishPreview(Request $request, AliExpressProductPreviewService $previewService): RedirectResponse
    {
        $validated = $request->validate([
            'preview_id' => 'required|integer|exists:aliexpress_product_previews,id',
            'category_id' => 'nullable|integer|exists:categories,id',
            'update_existing' => 'nullable|boolean',
        ]);

        try {
            $preview = AliExpressProductPreview::query()->findOrFail($validated['preview_id']);
            if (isset($validated['category_id'])) {
                $preview->update(['category_id' => (int) $validated['category_id']]);
            }
            $result = $previewService->publishFromPreview($preview, (bool) ($validated['update_existing'] ?? false), auth('admin')->id());
            Toastr::success('AliExpress preview publish status: ' . $result['status'] . '. Store product ID: ' . $result['product']->id);
            return redirect()->route('admin.aliexpress.preview.show', [$preview->id]);
        } catch (\Throwable $exception) {
            Toastr::error($exception->getMessage());
            return back();
        }
    }

    public function skipPreview(Request $request, AliExpressProductPreviewService $previewService): RedirectResponse
    {
        $validated = $request->validate([
            'preview_id' => 'required|integer|exists:aliexpress_product_previews,id',
            'message' => 'nullable|string|max:1000',
        ]);

        try {
            $preview = AliExpressProductPreview::query()->findOrFail($validated['preview_id']);
            $previewService->skipPreview($preview, $validated['message'] ?? null, auth('admin')->id());
            Toastr::success('AliExpress preview skipped.');
            return redirect()->route('admin.aliexpress.preview.show', [$preview->id]);
        } catch (\Throwable $exception) {
            Toastr::error($exception->getMessage());
            return back();
        }
    }

    public function publishImported(Request $request, AliExpressStoreProductPublisher $publisher): RedirectResponse
    {
        $validated = $request->validate([
            'ali_express_product_id' => 'required|string|max:30',
            'category_id' => 'nullable|integer|exists:categories,id',
        ]);

        try {
            $product = AliExpressProduct::query()
                ->where('ali_express_product_id', $validated['ali_express_product_id'])
                ->firstOrFail();
            $result = $publisher->publishWithStatus($product, isset($validated['category_id']) ? (int) $validated['category_id'] : null);
            Toastr::success('AliExpress product publish status: ' . $result['status'] . '. Store product ID: ' . $result['product']->id);
        } catch (\Throwable $exception) {
            Toastr::error($exception->getMessage());
        }

        return redirect()->route('admin.aliexpress.index');
    }

    public function importAndPublish(Request $request, AliExpressProductImporter $importer, AliExpressStoreProductPublisher $publisher): RedirectResponse
    {
        $validated = $request->validate([
            'product_input' => 'required|string|max:500',
            'category_id' => 'nullable|integer|exists:categories,id',
        ]);

        try {
            $productId = $this->extractProductId($validated['product_input']);
            $imported = $importer->import($productId);
            $storeProduct = $publisher->publish($imported, isset($validated['category_id']) ? (int) $validated['category_id'] : null);

            Toastr::success('AliExpress product imported and published. Store product ID: ' . $storeProduct->id);
        } catch (\Throwable $exception) {
            Toastr::error($exception->getMessage());
        }

        return redirect()->route('admin.aliexpress.index');
    }

    public function queueBulk(Request $request): RedirectResponse
    {
        if (!Schema::hasTable('aliexpress_import_queues')) {
            Toastr::error('AliExpress queue table is missing. Run: php artisan migrate');
            return redirect()->route('admin.aliexpress.index');
        }

        $validated = $request->validate([
            'bulk_inputs' => 'required|string',
            'category_id' => 'nullable|integer|exists:categories,id',
        ]);

        $lines = preg_split('/\r\n|\r|\n/', trim($validated['bulk_inputs'])) ?: [];
        $queued = 0;
        $failed = 0;
        $categoryId = isset($validated['category_id']) ? (int) $validated['category_id'] : null;
        $batchId = 'ae-' . now()->format('YmdHis') . '-' . Str::random(8);

        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '') {
                continue;
            }

            try {
                $productId = $this->extractProductId($line);
                $batchId = $batchId ?? 'ae-' . now()->format('YmdHis') . '-' . Str::random(8);
                $row = AliExpressImportQueue::query()->create([
                    'batch_id' => $batchId,
                    'source_input' => $line,
                    'ali_express_product_id' => $productId,
                    'status' => 'pending',
                    'attempts' => 0,
                    'message' => 'Queued for import and publish.',
                    'queued_by_admin_id' => auth('admin')->id(),
                    'created_by' => auth('admin')->id(),
                ]);

                AliExpressImportPublishJob::dispatch($row->id, $productId, $categoryId);
                $queued++;
            } catch (\Throwable $exception) {
                AliExpressImportQueue::query()->create([
                    'batch_id' => $batchId ?? ('ae-' . now()->format('YmdHis') . '-' . Str::random(8)),
                    'source_input' => $line,
                    'status' => 'failed',
                    'message' => $exception->getMessage(),
                    'error_message' => $exception->getMessage(),
                    'queued_by_admin_id' => auth('admin')->id(),
                    'created_by' => auth('admin')->id(),
                    'finished_at' => now(),
                ]);
                $failed++;
            }
        }

        if ($queued > 0) {
            Toastr::success("Queued {$queued} product(s) for background import.");
        }
        if ($failed > 0) {
            Toastr::warning("{$failed} line(s) failed validation. Check status table below.");
        }

        return redirect()->route('admin.aliexpress.index');
    }

    public function retryFailed(): RedirectResponse
    {
        if (!Schema::hasTable('aliexpress_import_queues')) {
            Toastr::error('AliExpress queue table is missing. Run: php artisan migrate');
            return redirect()->route('admin.aliexpress.index');
        }

        $rows = AliExpressImportQueue::query()
            ->whereIn('status', ['failed'])
            ->whereNotNull('ali_express_product_id')
            ->limit(100)
            ->get();

        foreach ($rows as $row) {
            $row->update([
                'status' => 'pending',
                'message' => 'Retry queued.',
                'error_message' => null,
                'cancelled_at' => null,
                'finished_at' => null,
            ]);
            AliExpressImportPublishJob::dispatch($row->id, $row->ali_express_product_id);
        }

        Toastr::success('Retried ' . $rows->count() . ' failed AliExpress queue item(s).');
        return redirect()->route('admin.aliexpress.index');
    }

    public function cancelPending(): RedirectResponse
    {
        if (!Schema::hasTable('aliexpress_import_queues')) {
            Toastr::error('AliExpress queue table is missing. Run: php artisan migrate');
            return redirect()->route('admin.aliexpress.index');
        }

        $count = AliExpressImportQueue::query()
            ->where('status', 'pending')
            ->update([
                'status' => 'cancelled',
                'message' => 'Cancelled by admin.',
                'cancelled_at' => now(),
                'finished_at' => now(),
            ]);

        Toastr::success('Cancelled ' . $count . ' pending AliExpress queue item(s).');
        return redirect()->route('admin.aliexpress.index');
    }

    private function extractProductId(string $input): string
    {
        $input = trim($input);

        if (preg_match('/\b(\d{10,20})\b/', $input, $matches) !== 1) {
            throw new RuntimeException('Could not detect AliExpress product ID from input: ' . $input);
        }

        return $matches[1];
    }
}
