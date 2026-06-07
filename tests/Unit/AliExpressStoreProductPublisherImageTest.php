<?php

namespace Tests\Unit;

use App\Models\AliExpressProduct;
use App\Models\Product;
use App\Services\AliExpress\AliExpressPricingService;
use App\Services\AliExpress\AliExpressProductPolicy;
use App\Services\AliExpress\AliExpressStoreProductPublisher;
use App\Services\IntegrationLogService;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use ReflectionMethod;
use Tests\TestCase;

class AliExpressStoreProductPublisherImageTest extends TestCase
{
    public function test_it_sets_thumbnail_from_first_downloaded_image(): void
    {
        Storage::fake('public');
        Http::fake([
            'img.example.test/*' => Http::response('image-bytes', 200, ['Content-Type' => 'image/jpeg']),
        ]);

        $assets = $this->prepareImageAssets([
            'https://img.example.test/first.jpg',
            'https://img.example.test/second.jpg',
        ]);

        $this->assertNotNull($assets['thumbnail']);
        $this->assertSame($assets['gallery'][0], $assets['thumbnail']);
        Storage::disk('public')->assertExists('product/thumbnail/' . $assets['thumbnail']);
    }

    public function test_it_saves_gallery_images_in_product_folder(): void
    {
        Storage::fake('public');
        Http::fake([
            'img.example.test/*' => Http::response('image-bytes', 200, ['Content-Type' => 'image/webp']),
        ]);

        $assets = $this->prepareImageAssets([
            'https://img.example.test/first.webp',
            'https://img.example.test/second.webp',
        ]);

        $this->assertCount(2, $assets['gallery']);
        foreach ($assets['gallery'] as $imageName) {
            Storage::disk('public')->assertExists('product/' . $imageName);
        }
    }

    public function test_it_reuses_existing_images_without_duplicate_downloads(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('product/existing-one.jpg', 'image');
        Storage::disk('public')->put('product/existing-two.jpg', 'image');
        Storage::disk('public')->put('product/thumbnail/existing-one.jpg', 'image');
        Http::fake();

        $existingProduct = new Product([
            'images' => json_encode([
                ['image_name' => 'existing-one.jpg', 'storage' => 'public'],
                ['image_name' => 'existing-two.jpg', 'storage' => 'public'],
            ], JSON_UNESCAPED_SLASHES),
            'thumbnail' => 'existing-one.jpg',
            'thumbnail_storage_type' => 'public',
        ]);

        $assets = $this->prepareImageAssets(['https://img.example.test/new.jpg'], $existingProduct);

        $this->assertSame('existing-one.jpg', $assets['thumbnail']);
        $this->assertSame(['existing-one.jpg', 'existing-two.jpg'], $assets['gallery']);
        Http::assertNothingSent();
    }

    public function test_failed_image_download_is_handled_safely(): void
    {
        Storage::fake('public');
        Http::fake([
            'img.example.test/*' => Http::response('', 500),
        ]);

        $assets = $this->prepareImageAssets(['https://img.example.test/missing.jpg']);

        $this->assertNull($assets['thumbnail']);
        $this->assertSame([], $assets['gallery']);
        $this->assertContains('image_download_failed', $assets['warnings']);
    }

    public function test_published_details_do_not_append_or_keep_source_line(): void
    {
        $source = new AliExpressProduct([
            'ali_express_product_id' => '1005001234567890',
            'description' => "<p>Public product content</p>\nSource: https://www.aliexpress.com/item/1005006422704079.html",
            'supplier_url' => 'https://www.aliexpress.com/item/1005006422704079.html',
            'supplier_product_url' => 'https://www.aliexpress.com/item/1005006422704079.html',
        ]);

        $method = new ReflectionMethod(AliExpressStoreProductPublisher::class, 'buildDetails');
        $method->setAccessible(true);
        $details = $method->invoke($this->publisher(), $source);

        $this->assertSame('<p>Public product content</p>', $details);
        $this->assertStringNotContainsString('Source:', $details);
        $this->assertStringNotContainsString('aliexpress.com/item', $details);
    }

    private function prepareImageAssets(array $images, ?Product $existingProduct = null): array
    {
        $source = new AliExpressProduct([
            'ali_express_product_id' => '1005001234567890',
            'images' => $images,
        ]);

        $method = new ReflectionMethod(AliExpressStoreProductPublisher::class, 'prepareImageAssets');
        $method->setAccessible(true);

        return $method->invoke($this->publisher(), $source, $existingProduct);
    }

    private function publisher(): AliExpressStoreProductPublisher
    {
        return new AliExpressStoreProductPublisher(
            new AliExpressPricingService(),
            new AliExpressProductPolicy(),
            new IntegrationLogService(),
        );
    }
}
