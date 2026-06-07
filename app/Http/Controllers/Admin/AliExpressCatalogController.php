<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\AliExpress\AliExpressCatalogBrowserService;
use App\Services\AliExpress\AliExpressCatalogProductBlockedException;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AliExpressCatalogController extends Controller
{
    public function index(Request $request, AliExpressCatalogBrowserService $catalog): View
    {
        return $this->renderCatalog($request, $catalog, false);
    }

    public function search(Request $request, AliExpressCatalogBrowserService $catalog): View
    {
        return $this->renderCatalog($request, $catalog, true);
    }

    public function preview(Request $request, AliExpressCatalogBrowserService $catalog, string $productId): RedirectResponse
    {
        try {
            $preview = $catalog->createPreviewFromProductId($this->validateProductId($productId), auth('admin')->id());
            Toastr::success('AliExpress preview created.');

            return redirect()->route('admin.aliexpress.preview.show', [$preview->id]);
        } catch (AliExpressCatalogProductBlockedException $exception) {
            Toastr::error($exception->getMessage());

            return redirect()->route('admin.aliexpress.preview.show', [$exception->preview->id]);
        } catch (\Throwable $exception) {
            Toastr::error($exception->getMessage());

            return redirect()->route('admin.aliexpress.catalog.index');
        }
    }

    public function import(Request $request, AliExpressCatalogBrowserService $catalog, string $productId): RedirectResponse
    {
        try {
            $result = $catalog->importFromCatalogResult($this->validateProductId($productId), auth('admin')->id());
            Toastr::success('AliExpress product imported from catalog.');

            return redirect()->route('admin.aliexpress.preview.show', [$result['preview']->id]);
        } catch (AliExpressCatalogProductBlockedException $exception) {
            Toastr::error($exception->getMessage());

            return redirect()->route('admin.aliexpress.preview.show', [$exception->preview->id]);
        } catch (\Throwable $exception) {
            Toastr::error($exception->getMessage());

            return redirect()->route('admin.aliexpress.catalog.index');
        }
    }

    public function publish(Request $request, AliExpressCatalogBrowserService $catalog, string $productId): RedirectResponse
    {
        try {
            $result = $catalog->publishFromCatalogResult($this->validateProductId($productId), auth('admin')->id());
            Toastr::success('AliExpress product published from catalog. Store product ID: ' . $result['product']->id);

            return redirect()->route('admin.aliexpress.preview.show', [$result['preview']->id]);
        } catch (AliExpressCatalogProductBlockedException $exception) {
            Toastr::error($exception->getMessage());

            return redirect()->route('admin.aliexpress.preview.show', [$exception->preview->id]);
        } catch (\Throwable $exception) {
            Toastr::error($exception->getMessage());

            return redirect()->route('admin.aliexpress.catalog.index');
        }
    }

    private function renderCatalog(Request $request, AliExpressCatalogBrowserService $catalog, bool $performSearch): View
    {
        $filters = $this->validatedFilters($request);
        $categories = $catalog->categories();
        $results = [
            'success' => true,
            'message' => $performSearch ? null : 'Search by keyword or choose a category to browse AliExpress products.',
            'items' => [],
            'meta' => ['current_page' => (int) ($filters['page'] ?? 1), 'per_page' => (int) ($filters['per_page'] ?? 20), 'total_results' => null, 'total_pages' => null],
            'warnings' => [],
            'unsupported' => false,
        ];

        if ($performSearch) {
            try {
                $results = $catalog->search($filters);
            } catch (\Throwable $exception) {
                $results = [
                    'success' => false,
                    'message' => 'AliExpress catalog search failed. You can still import by product URL or ID.',
                    'items' => [],
                    'meta' => ['current_page' => (int) ($filters['page'] ?? 1), 'per_page' => (int) ($filters['per_page'] ?? 20), 'total_results' => null, 'total_pages' => null],
                    'warnings' => [$exception->getMessage()],
                    'unsupported' => true,
                ];
            }
        }

        return view('admin-views.integrations.aliexpress.catalog', [
            'filters' => $filters,
            'categories' => $categories,
            'results' => $results,
        ]);
    }

    private function validatedFilters(Request $request): array
    {
        return $request->validate([
            'keyword' => 'nullable|string|max:255',
            'category_id' => 'nullable|string|max:100',
            'min_price' => 'nullable|numeric|min:0',
            'max_price' => 'nullable|numeric|min:0',
            'min_rating' => 'nullable|numeric|min:0|max:5',
            'min_orders' => 'nullable|integer|min:0',
            'ship_to_country' => 'nullable|string|max:2',
            'sort' => 'nullable|string|in:relevance,price_asc,price_desc,orders_desc,rating_desc',
            'page' => 'nullable|integer|min:1',
            'per_page' => 'nullable|integer|min:1|max:50',
        ]);
    }

    private function validateProductId(string $productId): string
    {
        if (preg_match('/^\d{10,20}$/', $productId) !== 1) {
            throw new \RuntimeException('Invalid AliExpress product ID.');
        }

        return $productId;
    }
}
