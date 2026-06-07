<?php

namespace App\Jobs;

use App\Models\AliExpressImportQueue;
use App\Services\AliExpress\AliExpressProductBlockedException;
use App\Services\AliExpress\AliExpressProductImporter;
use App\Services\AliExpress\AliExpressStoreProductPublisher;
use App\Services\IntegrationLogService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use RuntimeException;

class AliExpressImportPublishJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public int $queueRowId,
        public string $productId,
        public ?int $categoryId = null,
    ) {
    }

    public function handle(AliExpressProductImporter $importer, AliExpressStoreProductPublisher $publisher, IntegrationLogService $integrationLog): void
    {
        $queueRow = AliExpressImportQueue::query()->find($this->queueRowId);
        if (!$queueRow) {
            return;
        }
        if ($queueRow->status === 'cancelled' || $queueRow->cancelled_at) {
            return;
        }

        $queueRow->update([
            'status' => 'processing',
            'attempts' => (int) $queueRow->attempts + 1,
            'started_at' => now(),
            'message' => 'Importing from AliExpress...',
        ]);
        $integrationLog->log('aliexpress', 'queue_job_started', 'processing', null, [
            'queue_id' => $queueRow->id,
            'batch_id' => $queueRow->batch_id,
        ], $this->productId, $queueRow->created_by ?? $queueRow->queued_by_admin_id);

        try {
            $aliProduct = $importer->import($this->productId);
            $publishResult = $publisher->publishWithStatus($aliProduct, $this->categoryId);
            $storeProduct = $publishResult['product'];

            $queueRow->update([
                'status' => 'success',
                'ali_express_product_id' => $this->productId,
                'store_product_id' => $storeProduct->id,
                'message' => 'Imported and published successfully.',
                'error_message' => null,
                'finished_at' => now(),
            ]);
        } catch (AliExpressProductBlockedException $exception) {
            $queueRow->update([
                'status' => 'blocked',
                'ali_express_product_id' => $this->productId,
                'message' => $exception->getMessage(),
                'error_message' => $exception->getMessage(),
                'finished_at' => now(),
            ]);
            $integrationLog->log('aliexpress', 'queue_job_failed', 'blocked', $exception->getMessage(), [
                'queue_id' => $queueRow->id,
                'reasons' => $exception->reasons,
            ], $this->productId, $queueRow->created_by ?? $queueRow->queued_by_admin_id);
        } catch (\Throwable $exception) {
            $queueRow->update([
                'status' => 'failed',
                'ali_express_product_id' => $this->productId,
                'message' => $exception->getMessage(),
                'error_message' => $exception->getMessage(),
                'finished_at' => now(),
            ]);
            $integrationLog->log('aliexpress', 'queue_job_failed', 'failed', $exception->getMessage(), [
                'queue_id' => $queueRow->id,
                'batch_id' => $queueRow->batch_id,
            ], $this->productId, $queueRow->created_by ?? $queueRow->queued_by_admin_id);

            throw new RuntimeException($exception->getMessage(), 0, $exception);
        }
    }
}
