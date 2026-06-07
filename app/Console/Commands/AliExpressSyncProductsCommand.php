<?php

namespace App\Console\Commands;

use App\Jobs\AliExpressSyncProductJob;
use App\Models\AliExpressProduct;
use App\Services\AliExpress\AliExpressProductSyncService;
use Illuminate\Console\Command;

class AliExpressSyncProductsCommand extends Command
{
    protected $signature = 'aliexpress:sync-products
                            {--product-id= : AliExpress product ID}
                            {--limit=50 : Maximum products to sync}
                            {--failed : Only sync previously failed products}
                            {--dry-run : Show changes without saving}';

    protected $description = 'Sync existing AliExpress product price, stock and availability';

    public function handle(AliExpressProductSyncService $syncService): int
    {
        $query = AliExpressProduct::query()->orderBy('id');

        if ($this->option('product-id')) {
            $query->where('ali_express_product_id', (string) $this->option('product-id'));
        }
        if ($this->option('failed')) {
            $query->where('sync_status', 'failed');
        }

        $products = $query->limit(max(1, (int) $this->option('limit')))->get();
        if ($products->isEmpty()) {
            $this->warn('No AliExpress products matched the selected filters.');
            return self::SUCCESS;
        }

        $failed = 0;
        foreach ($products as $product) {
            if (!$this->option('dry-run')) {
                AliExpressSyncProductJob::dispatch($product->id);
                $this->line('Queued sync for AE ' . $product->ali_express_product_id);
                continue;
            }

            try {
                $result = $syncService->sync($product, true);
                $this->line('Dry run AE ' . $product->ali_express_product_id . ': ' . json_encode($result['changes'], JSON_UNESCAPED_SLASHES));
            } catch (\Throwable $throwable) {
                $failed++;
                $this->error('Failed AE ' . $product->ali_express_product_id . ': ' . $throwable->getMessage());
            }
        }

        return $failed > 0 ? self::FAILURE : self::SUCCESS;
    }
}
