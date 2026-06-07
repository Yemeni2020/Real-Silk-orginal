<?php

namespace Tests\Unit;

use App\Models\AliExpressProductPreview;
use App\Services\AliExpress\AliExpressCatalogBrowserService;
use App\Services\AliExpress\AliExpressClient;
use App\Services\AliExpress\AliExpressCatalogProductBlockedException;
use App\Services\AliExpress\AliExpressProductBlockedException;
use App\Services\AliExpress\AliExpressProductPreviewService;
use App\Services\AliExpress\AliExpressTokenStore;
use App\Services\IntegrationLogService;
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class AliExpressCatalogBrowserServiceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();
        Schema::dropIfExists('aliexpress_product_previews');
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
    }

    public function test_search_products_client_method_uses_fake_response(): void
    {
        $mock = new MockHandler([
            new Response(200, [], json_encode([
                'aliexpress_ds_text_search_response' => [
                    'result' => [
                        'total_record_count' => 1,
                        'products' => ['product' => [['product_id' => '1005001234567890', 'product_title' => 'Dress']]],
                    ],
                ],
            ])),
        ]);
        $http = new GuzzleClient(['handler' => HandlerStack::create($mock)]);
        $client = new AliExpressClient('key', 'secret', 'https://example.test/sync', 'https://example.test/rest', null, null, $http);

        $result = $client->searchProducts(['keyword' => 'dress', 'page' => 1, 'per_page' => 20]);
        $request = $mock->getLastRequest();

        $this->assertStringContainsString('currency=USD', (string) $request->getUri());
        $this->assertStringContainsString('local=en_US', (string) $request->getUri());
        $this->assertTrue($result['success']);
        $this->assertSame('Dress', $result['data']['aliexpress_ds_text_search_response']['result']['products']['product'][0]['product_title']);
    }

    public function test_catalog_service_normalizes_search_results(): void
    {
        $service = $this->service(searchResponse: [
            'success' => true,
            'data' => [
                'result' => [
                    'total_record_count' => 1,
                    'products' => [[
                        'product_id' => '1005001234567890',
                        'product_title' => 'Silk scarf',
                        'product_main_image_url' => '//img.test/a.jpg',
                        'target_sale_price' => '12.50',
                        'target_sale_price_currency' => 'USD',
                        'sale_count' => 42,
                    ]],
                ],
            ],
            'warnings' => [],
        ]);

        $result = $service->search(['keyword' => 'scarf']);

        $this->assertTrue($result['success']);
        $this->assertSame('1005001234567890', $result['items'][0]['product_id']);
        $this->assertSame('https://img.test/a.jpg', $result['items'][0]['image']);
        $this->assertSame(12.5, $result['items'][0]['price']);
        $this->assertSame(42, $result['items'][0]['orders']);
    }


    public function test_catalog_service_normalizes_actual_selection_search_shape(): void
    {
        $service = $this->service(searchResponse: [
            'success' => true,
            'data' => [
                'aliexpress_ds_text_search_response' => [
                    'data' => [
                        'pageIndex' => 1,
                        'pageSize' => 10,
                        'totalCount' => 7210,
                        'products' => [
                            'selection_search_product' => [[
                                'itemId' => '1005010119248627',
                                'title' => 'Wireless Mouse',
                                'itemMainPic' => 'https://img.test/mouse.jpg',
                                'targetSalePrice' => '5.21',
                                'targetOriginalPriceCurrency' => 'USD',
                                'score' => '4.7',
                                'orders' => '5,000+',
                                'cateId' => '44,100000310,202177812',
                            ]],
                        ],
                    ],
                ],
            ],
            'warnings' => [],
        ]);

        $result = $service->search(['keyword' => 'mouse']);

        $this->assertSame('1005010119248627', $result['items'][0]['product_id']);
        $this->assertSame('Wireless Mouse', $result['items'][0]['title']);
        $this->assertSame(5.21, $result['items'][0]['price']);
        $this->assertSame(4.7, $result['items'][0]['rating']);
        $this->assertSame(5000, $result['items'][0]['orders']);
        $this->assertSame(7210, $result['meta']['total_results']);
    }

    public function test_empty_search_does_not_call_api(): void
    {
        $client = $this->getMockBuilder(AliExpressClient::class)->disableOriginalConstructor()->onlyMethods(['searchProducts'])->getMock();
        $client->expects($this->never())->method('searchProducts');

        $result = $this->service(client: $client)->search([]);

        $this->assertTrue($result['success']);
        $this->assertSame([], $result['items']);
        $this->assertStringContainsString('Search by keyword', $result['message']);
    }

    public function test_api_failure_returns_friendly_error(): void
    {
        $service = $this->service(searchResponse: [
            'success' => false,
            'message' => 'AliExpress catalog search is not available for this API account. You can still import by product URL or ID.',
            'warnings' => ['permission denied'],
        ]);

        $result = $service->search(['keyword' => 'watch']);

        $this->assertFalse($result['success']);
        $this->assertTrue($result['unsupported']);
        $this->assertStringContainsString('not available', $result['message']);
    }

    public function test_preview_from_catalog_result_creates_persisted_preview(): void
    {
        $preview = AliExpressProductPreview::query()->create([
            'source_input' => '1005001234567890',
            'ali_express_product_id' => '1005001234567890',
            'title' => 'Preview product',
            'policy_status' => 'allowed',
        ]);
        $previewService = $this->previewService();
        $previewService->expects($this->once())->method('createPreview')->willReturn($preview);

        $created = $this->service(previewService: $previewService)->createPreviewFromProductId('1005001234567890', 1);

        $this->assertSame($preview->id, $created->id);
    }

    public function test_import_from_catalog_result_uses_preview_import_flow(): void
    {
        $preview = AliExpressProductPreview::query()->create([
            'source_input' => '1005001234567890',
            'ali_express_product_id' => '1005001234567890',
            'title' => 'Preview product',
            'policy_status' => 'allowed',
        ]);
        $product = new \App\Models\AliExpressProduct();
        $previewService = $this->previewService();
        $previewService->method('createPreview')->willReturn($preview);
        $previewService->expects($this->once())->method('importFromPreview')->willReturn($product);

        $result = $this->service(previewService: $previewService)->importFromCatalogResult('1005001234567890');

        $this->assertSame($preview->id, $result['preview']->id);
        $this->assertSame($product, $result['product']);
    }

    public function test_publish_from_catalog_result_uses_preview_publish_flow(): void
    {
        $preview = AliExpressProductPreview::query()->create([
            'source_input' => '1005001234567890',
            'ali_express_product_id' => '1005001234567890',
            'title' => 'Preview product',
            'policy_status' => 'allowed',
        ]);
        $previewService = $this->previewService();
        $previewService->method('createPreview')->willReturn($preview);
        $previewService->expects($this->once())->method('publishFromPreview')->willReturn(['status' => 'created', 'product' => (object) ['id' => 5]]);

        $result = $this->service(previewService: $previewService)->publishFromCatalogResult('1005001234567890');

        $this->assertSame('created', $result['status']);
        $this->assertSame($preview->id, $result['preview']->id);
    }

    public function test_blocked_product_from_catalog_does_not_publish(): void
    {
        $preview = AliExpressProductPreview::query()->create([
            'source_input' => '1005001234567890',
            'ali_express_product_id' => '1005001234567890',
            'title' => 'Blocked product',
            'policy_status' => 'blocked',
            'block_reasons' => ['blocked_keyword:weapon'],
        ]);
        $previewService = $this->previewService();
        $previewService->method('createPreview')->willReturn($preview);
        $previewService->expects($this->once())->method('publishFromPreview')->willThrowException(new AliExpressProductBlockedException(['blocked_keyword:weapon']));

        $this->expectException(AliExpressCatalogProductBlockedException::class);
        $this->service(previewService: $previewService)->publishFromCatalogResult('1005001234567890');
    }

    public function test_category_list_caching(): void
    {
        $client = $this->getMockBuilder(AliExpressClient::class)->disableOriginalConstructor()->onlyMethods(['getCategories'])->getMock();
        $client->expects($this->once())->method('getCategories')->willReturn([
            'success' => true,
            'data' => ['result' => [['category_id' => '1', 'category_name' => 'Fashion']]],
            'warnings' => [],
        ]);

        $service = $this->service(client: $client);
        $this->assertCount(1, $service->categories()['items']);
        $this->assertCount(1, $service->categories()['items']);
    }

    private function service(?AliExpressClient $client = null, ?AliExpressProductPreviewService $previewService = null, ?array $searchResponse = null): AliExpressCatalogBrowserService
    {
        if (!$client) {
            $client = $this->getMockBuilder(AliExpressClient::class)->disableOriginalConstructor()->onlyMethods(['searchProducts', 'getCategories'])->getMock();
            $client->method('getCategories')->willReturn(['success' => false, 'message' => 'unsupported', 'data' => [], 'warnings' => []]);
        }
        if ($searchResponse !== null) {
            $client->method('searchProducts')->willReturn($searchResponse);
        }

        $tokenStore = $this->getMockBuilder(AliExpressTokenStore::class)->disableOriginalConstructor()->onlyMethods(['getValidAccessToken'])->getMock();
        $tokenStore->method('getValidAccessToken')->willReturn('token');

        return new AliExpressCatalogBrowserService(
            $client,
            $tokenStore,
            $previewService ?: $this->previewService(),
            new IntegrationLogService(),
        );
    }

    private function previewService(): AliExpressProductPreviewService&\PHPUnit\Framework\MockObject\MockObject
    {
        return $this->getMockBuilder(AliExpressProductPreviewService::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['createPreview', 'importFromPreview', 'publishFromPreview'])
            ->getMock();
    }
}
