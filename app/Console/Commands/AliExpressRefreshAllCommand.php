<?php

namespace App\Console\Commands;

use App\Models\AliExpressProduct;
use App\Models\Product;
use App\Services\AliExpress\AliExpressProductImporter;
use App\Services\AliExpress\AliExpressStoreProductPublisher;
use Illuminate\Console\Command;

class AliExpressRefreshAllCommand extends Command
{
    protected $signature = 'aliexpress:refresh-all
                            {--chunk=50 : Batch size}
                            {--sleep-ms=0 : Delay between items in milliseconds}
                            {--from-id= : Start from local aliexpress_products.id}
                            {--to-id= : End at local aliexpress_products.id}
                            {--category-id= : Force category_id for all published products}
                            {--stop-on-error : Stop on first error}';

    protected $description = 'Re-import and publish all previously imported AliExpress products';

    public function handle(AliExpressProductImporter $importer, AliExpressStoreProductPublisher $publisher): int
    {
        $chunk = max(1, (int) $this->option('chunk'));
        $sleepMs = max(0, (int) $this->option('sleep-ms'));
        $forcedCategoryId = $this->option('category-id') !== null ? (int) $this->option('category-id') : null;
        $stopOnError = (bool) $this->option('stop-on-error');

        $query = AliExpressProduct::query()->orderBy('id');
        if ($this->option('from-id') !== null) {
            $query->where('id', '>=', (int) $this->option('from-id'));
        }
        if ($this->option('to-id') !== null) {
            $query->where('id', '<=', (int) $this->option('to-id'));
        }

        $total = (clone $query)->count();
        if ($total === 0) {
            $this->warn('No imported AliExpress products found for the selected filter.');
            return self::SUCCESS;
        }

        $this->info("Refreshing {$total} AliExpress product(s)...");

        $processed = 0;
        $ok = 0;
        $failed = 0;
        $errors = [];

        $query->chunkById($chunk, function ($rows) use (
            $importer,
            $publisher,
            $sleepMs,
            $forcedCategoryId,
            $stopOnError,
            &$processed,
            &$ok,
            &$failed,
            &$errors
        ) {
            foreach ($rows as $source) {
                $processed++;
                $aliExpressId = (string) $source->ali_express_product_id;

                try {
                    $imported = $importer->import($aliExpressId);
                    $existingProduct = Product::query()->where('code', 'AE-' . $aliExpressId)->first(['category_id']);
                    $categoryId = $forcedCategoryId ?? ($existingProduct?->category_id ?: null);

                    $product = $publisher->publish($imported, $categoryId);
                    $ok++;

                    $this->line("[{$processed}] OK AE {$aliExpressId} -> Product {$product->id} | Price {$product->unit_price} | Stock {$product->current_stock}");
                } catch (\Throwable $throwable) {
                    $failed++;
                    $message = "[{$processed}] FAIL AE {$aliExpressId}: {$throwable->getMessage()}";
                    $errors[] = $message;
                    $this->error($message);

                    if ($stopOnError) {
                        return false;
                    }
                }

                if ($sleepMs > 0) {
                    usleep($sleepMs * 1000);
                }
            }

            return true;
        });

        $this->newLine();
        $this->info("Done. Processed: {$processed}, Success: {$ok}, Failed: {$failed}");

        if (!empty($errors)) {
            $this->warn('Failure summary:');
            foreach ($errors as $error) {
                $this->line($error);
            }
        }

        return $failed > 0 ? self::FAILURE : self::SUCCESS;
    }
}

