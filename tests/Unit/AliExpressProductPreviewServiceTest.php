<?php

namespace Tests\Unit;

use App\Models\AliExpressProduct;
use App\Models\AliExpressProductPreview;
use App\Services\AliExpress\AliExpressClient;
use App\Services\AliExpress\AliExpressPricingService;
use App\Services\AliExpress\AliExpressProductBlockedException;
use App\Services\AliExpress\AliExpressProductNormalizer;
use App\Services\AliExpress\AliExpressProductPolicy;
use App\Services\AliExpress\AliExpressProductPreviewService;
use App\Services\AliExpress\AliExpressStoreProductPublisher;
use App\Services\AliExpress\AliExpressTokenStore;
use App\Services\IntegrationLogService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class AliExpressProductPreviewServiceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::dropIfExists('aliexpress_product_previews');
        Schema::dropIfExists('aliexpress_products');

        Schema::create('aliexpress_product_previews', function (Blueprint $table) {
            $table->id();
            $table->text('source_input');
            $table->string('ali_express_product_id');
            $table->string('title')->nullable();
            $table->string('normalized_title')->nullable();
            $table->decimal('supplier_price', 12, 2)->nullable();
            $table->decimal('supplier_shipping_price', 12, 2)->nullable();
            $table->decimal('final_price', 12, 2)->nullable();
            $table->decimal('estimated_profit', 12, 2)->nullable();
            $table->string('currency', 10)->nullable();
            $table->json('images')->nullable();
            $table->json('variants')->nullable();
            $table->text('supplier_url')->nullable();
            $table->string('availability_status', 50)->nullable();
            $table->json('warnings')->nullable();
            $table->json('block_reasons')->nullable();
            $table->string('policy_status', 30)->default('allowed');
            $table->json('pricing_payload')->nullable();
            $table->json('normalized_payload')->nullable();
            $table->json('raw_payload')->nullable();
            $table->string('status', 30)->default('previewed');
            $table->unsignedBigInteger('category_id')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('local_product_id')->nullable();
            $table->text('message')->nullable();
            $table->timestamp('imported_at')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->timestamp('skipped_at')->nullable();
            $table->timestamps();
        });

        Schema::create('aliexpress_products', function (Blueprint $table) {
            $table->id();
            $table->string('ali_express_product_id')->unique();
            $table->unsignedBigInteger('local_product_id')->nullable();
            $table->string('title')->nullable();
            $table->longText('description')->nullable();
            $table->decimal('supplier_price', 12, 2)->nullable();
            $table->decimal('supplier_shipping_price', 12, 2)->nullable();
            $table->decimal('selling_price', 12, 2)->nullable();
            $table->decimal('margin', 8, 2)->default(0);
            $table->unsignedInteger('stock')->default(0);
            $table->string('currency', 10)->nullable();
            $table->string('supplier_currency', 10)->nullable();
            $table->json('images')->nullable();
            $table->json('variants')->nullable();
            $table->json('variant_mappings')->nullable();
            $table->text('supplier_url')->nullable();
            $table->text('supplier_product_url')->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('is_available')->default(true);
            $table->timestamp('last_synced_at')->nullable();
            $table->string('sync_status', 30)->default('pending');
            $table->text('sync_error')->nullable();
            $table->text('block_reason')->nullable();
            $table->json('warning_flags')->nullable();
            $table->string('supplier_stock_status', 50)->nullable();
            $table->timestamp('source_updated_at')->nullable();
            $table->json('raw_payload')->nullable();
            $table->timestamps();
        });

        config([
            'aliexpress.default_margin' => 25,
            'aliexpress.pricing.markup_type' => 'percentage',
            'aliexpress.pricing.markup_value' => 25,
            'aliexpress.pricing.minimum_profit' => 0,
            'aliexpress.pricing.include_shipping_cost' => false,
            'aliexpress.pricing.rounding_rule' => 'none',
            'aliexpress.policy.blocked_keywords' => ['weapon'],
            'aliexpress.policy.suspicious_brand_terms' => [],
        ]);
    }

    public function test_extracts_product_id_from_url_or_raw_id(): void
    {
        $service = $this->service();

        $this->assertSame('1005001234567890', $service->extractProductId('https://www.aliexpress.com/item/1005001234567890.html'));
        $this->assertSame('1005001234567890', $service->extractProductId('1005001234567890'));
    }

    public function test_creates_preview_from_api_payload(): void
    {
        $service = $this->service($this->payload());

        $preview = $service->createPreview('https://www.aliexpress.com/item/1005001234567890.html', 7, 1);

        $this->assertDatabaseHas('aliexpress_product_previews', [
            'id' => $preview->id,
            'ali_express_product_id' => '1005001234567890',
            'policy_status' => 'allowed',
            'status' => 'previewed',
            'category_id' => 7,
        ]);
        $this->assertSame('Example Product', $preview->title);
        $this->assertSame(12.25, $preview->supplier_price);
        $this->assertSame(15.31, $preview->final_price);
        $this->assertSame(3.06, $preview->estimated_profit);
        $this->assertCount(2, $preview->images);
        $this->assertCount(1, $preview->variants);
    }

    public function test_blocked_preview_is_stored_but_not_publishable(): void
    {
        $service = $this->service($this->payload('weapon product'));
        $preview = $service->createPreview('1005001234567890');

        $this->assertSame('blocked', $preview->policy_status);
        $this->assertContains('blocked_keyword:weapon', $preview->block_reasons);

        $this->expectException(AliExpressProductBlockedException::class);
        $service->publishFromPreview($preview->fresh());
    }

    public function test_imports_from_preview_without_publishing(): void
    {
        $service = $this->service($this->payload());
        $preview = $service->createPreview('1005001234567890');

        $product = $service->importFromPreview($preview);

        $this->assertInstanceOf(AliExpressProduct::class, $product);
        $this->assertDatabaseHas('aliexpress_products', [
            'ali_express_product_id' => '1005001234567890',
            'title' => 'Example Product',
        ]);
        $this->assertSame('imported', $preview->fresh()->status);
    }

    public function test_publishes_from_preview_using_idempotent_publisher(): void
    {
        $productObject = new class {
            public int $id = 321;
        };
        $publisher = $this->getMockBuilder(AliExpressStoreProductPublisher::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['publishWithStatus'])
            ->getMock();
        $publisher->expects($this->once())
            ->method('publishWithStatus')
            ->willReturn(['status' => 'created', 'product' => $productObject, 'warnings' => []]);

        $service = $this->service($this->payload(), $publisher);
        $preview = $service->createPreview('1005001234567890');
        $result = $service->publishFromPreview($preview->fresh());

        $this->assertSame('created', $result['status']);
        $this->assertSame('published', $preview->fresh()->status);
        $this->assertSame(321, $preview->fresh()->local_product_id);
    }

    private function service(?array $payload = null, ?AliExpressStoreProductPublisher $publisher = null): AliExpressProductPreviewService
    {
        $client = $this->getMockBuilder(AliExpressClient::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getDropshippingProduct'])
            ->getMock();
        $client->method('getDropshippingProduct')->willReturn($payload ?? $this->payload());

        $tokenStore = $this->getMockBuilder(AliExpressTokenStore::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getValidAccessToken'])
            ->getMock();
        $tokenStore->method('getValidAccessToken')->willReturn('access-token');

        $publisher = $publisher ?: $this->getMockBuilder(AliExpressStoreProductPublisher::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['publishWithStatus'])
            ->getMock();

        return new AliExpressProductPreviewService(
            $client,
            $tokenStore,
            new AliExpressProductNormalizer(),
            new AliExpressPricingService(),
            new AliExpressProductPolicy(),
            $publisher,
            new IntegrationLogService(),
        );
    }

    private function payload(string $title = 'Example Product'): array
    {
        return [
            'aliexpress_ds_product_get_response' => [
                'result' => [
                    'ae_item_base_info_dto' => [
                        'product_id' => '1005001234567890',
                        'subject' => $title,
                        'detail' => '<p>Example description</p>',
                        'currency_code' => 'USD',
                        'product_status_type' => 'onSelling',
                    ],
                    'ae_item_sku_info_dtos' => [
                        [
                            'id' => 'sku-1',
                            'sku_code' => 'BLUE-M',
                            'sku_price' => '15.50',
                            'offer_sale_price' => '12.25',
                            'currency_code' => 'USD',
                            'sku_available_stock' => 8,
                            'ae_sku_property_dtos' => [
                                ['sku_property_name' => 'Color', 'sku_property_value' => 'Blue'],
                            ],
                        ],
                    ],
                    'ae_multimedia_info_dto' => [
                        'image_urls' => 'https://img1.test/a.jpg;https://img1.test/b.jpg',
                    ],
                ],
            ],
        ];
    }
}
