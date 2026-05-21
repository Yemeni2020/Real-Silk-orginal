<?php

namespace App\Console\Commands;

use App\Services\AliExpress\AliExpressProductImporter;
use Illuminate\Console\Command;
use RuntimeException;

class AliExpressImportProductCommand extends Command
{
    protected $signature = 'aliexpress:import-product
                            {productId : AliExpress product ID}
                            {--country= : Ship-to country code, for example US}
                            {--currency= : Target currency, for example USD}
                            {--language= : Target language, for example EN}
                            {--margin= : Selling margin percentage}';

    protected $description = 'Import a single AliExpress dropshipping product into aliexpress_products';

    public function handle(AliExpressProductImporter $importer): int
    {
        try {
            $product = $importer->import(
                (string) $this->argument('productId'),
                $this->option('country') ?: null,
                $this->option('currency') ?: null,
                $this->option('language') ?: null,
                $this->option('margin') !== null ? (float) $this->option('margin') : null,
            );
        } catch (RuntimeException $exception) {
            $this->error($exception->getMessage());

            return self::FAILURE;
        }

        $this->info('Imported AliExpress product #' . $product->ali_express_product_id);
        $this->line('Title: ' . $product->title);
        $this->line('Supplier price: ' . ($product->supplier_price ?? 'n/a'));
        $this->line('Selling price: ' . ($product->selling_price ?? 'n/a'));
        $this->line('Stock: ' . ($product->stock ?? 'n/a'));

        return self::SUCCESS;
    }
}
