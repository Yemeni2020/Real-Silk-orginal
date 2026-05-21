<?php

namespace Tests\Unit;

use App\Services\AliExpress\AliExpressProductNormalizer;
use Tests\TestCase;

class AliExpressProductNormalizerTest extends TestCase
{
    public function test_it_normalizes_aliexpress_product_payload(): void
    {
        $normalizer = new AliExpressProductNormalizer();

        $normalized = $normalizer->normalize([
            'aliexpress_ds_product_get_response' => [
                'result' => [
                    'ae_item_base_info_dto' => [
                        'product_id' => '1005001234567890',
                        'subject' => 'Example Product',
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
                                [
                                    'sku_property_name' => 'Color',
                                    'sku_property_value' => 'Blue',
                                ],
                            ],
                        ],
                        [
                            'id' => 'sku-2',
                            'sku_code' => 'RED-L',
                            'sku_price' => '16.00',
                            'currency_code' => 'USD',
                            'sku_available_stock' => 5,
                        ],
                    ],
                    'ae_multimedia_info_dto' => [
                        'image_urls' => 'https://img1.test/a.jpg;https://img1.test/b.jpg',
                    ],
                ],
            ],
        ], 25);

        $this->assertSame('1005001234567890', $normalized['ali_express_product_id']);
        $this->assertSame('Example Product', $normalized['title']);
        $this->assertSame(12.25, $normalized['supplier_price']);
        $this->assertSame(15.31, $normalized['selling_price']);
        $this->assertSame(13, $normalized['stock']);
        $this->assertSame('https://www.aliexpress.com/item/1005001234567890.html', $normalized['supplier_url']);
        $this->assertCount(2, $normalized['images']);
        $this->assertCount(2, $normalized['variants']);
        $this->assertTrue($normalized['is_active']);
    }
}
