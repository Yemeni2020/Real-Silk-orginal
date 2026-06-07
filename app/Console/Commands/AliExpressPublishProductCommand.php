<?php

namespace App\Console\Commands;

use App\Models\AliExpressProduct;
use App\Services\AliExpress\AliExpressStoreProductPublisher;
use Illuminate\Console\Command;
use RuntimeException;

class AliExpressPublishProductCommand extends Command
{
    protected $signature = 'aliexpress:publish-product
                            {productId : AliExpress product ID}
                            {--category-id= : Category ID for the storefront product}
                            {--sub-category-id= : Optional sub category ID}
                            {--sub-sub-category-id= : Optional sub sub category ID}
                            {--user-id=1 : Product owner user_id (admin = 1)}
                            {--added-by=admin : Product owner type (admin or seller)}
                            {--status=1 : Product status (1 active, 0 inactive)}
                            {--request-status=1 : Request status (1 approved)}';

    protected $description = 'Create or update a storefront product from aliexpress_products';

    public function handle(AliExpressStoreProductPublisher $publisher): int
    {
        try {
            $source = AliExpressProduct::query()
                ->where('ali_express_product_id', (string) $this->argument('productId'))
                ->first();

            if (!$source) {
                throw new RuntimeException('AliExpress product not found in local table. Run aliexpress:import-product first.');
            }

            $categoryId = $this->toNullableInt($this->option('category-id'));
            $subCategoryId = $this->toNullableInt($this->option('sub-category-id'));
            $subSubCategoryId = $this->toNullableInt($this->option('sub-sub-category-id'));
            $product = $publisher->publish(
                source: $source,
                categoryId: $categoryId,
                subCategoryId: $subCategoryId,
                subSubCategoryId: $subSubCategoryId,
                userId: (int) $this->option('user-id'),
                addedBy: (string) $this->option('added-by'),
                status: (int) $this->option('status'),
                requestStatus: (int) $this->option('request-status'),
            );
        } catch (\Throwable $throwable) {
            $this->error($throwable->getMessage());

            return self::FAILURE;
        }

        $this->info('Store product synced successfully.');
        $this->line('Product ID: ' . $product->id);
        $this->line('AliExpress ID: ' . $source->ali_express_product_id);
        $this->line('Slug: ' . $product->slug);
        $this->line('Status: ' . $product->status . ', Request status: ' . $product->request_status);
        $this->line('Stock: ' . $product->current_stock . ', Price: ' . $product->unit_price);

        return self::SUCCESS;
    }

    private function toNullableInt(mixed $value): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }

        return (int) $value;
    }
}
