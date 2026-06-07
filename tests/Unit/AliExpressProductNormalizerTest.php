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

    public function test_it_normalizes_protocol_relative_and_invalid_image_urls(): void
    {
        $normalizer = new AliExpressProductNormalizer();

        $normalized = $normalizer->normalize([
            'aliexpress_ds_product_get_response' => [
                'result' => [
                    'ae_item_base_info_dto' => [
                        'product_id' => '1005001234567890',
                        'subject' => 'Example Product',
                        'currency_code' => 'USD',
                        'product_status_type' => 'onSelling',
                    ],
                    'ae_item_sku_info_dtos' => [
                        [
                            'sku_price' => '10.00',
                            'currency_code' => 'USD',
                            'sku_available_stock' => 1,
                        ],
                    ],
                    'ae_multimedia_info_dto' => [
                        'image_urls' => [
                            ' //img.example.test/a.jpg ',
                            '',
                            'not-a-url',
                            'https://img.example.test/b.webp',
                        ],
                    ],
                ],
            ],
        ], 25);

        $this->assertSame([
            'https://img.example.test/a.jpg',
            'https://img.example.test/b.webp',
        ], $normalized['images']);
    }

    public function test_it_removes_plain_text_source_lines_from_description(): void
    {
        $cleaned = AliExpressProductNormalizer::cleanDescription(
            "High quality mouse\nSource: https://www.aliexpress.com/item/1005006422704079.html"
        );

        $this->assertSame('High quality mouse', $cleaned);
        $this->assertStringNotContainsString('Source:', $cleaned);
        $this->assertStringNotContainsString('aliexpress.com/item', $cleaned);
    }

    public function test_it_removes_br_and_paragraph_source_lines_without_removing_content(): void
    {
        $brCleaned = AliExpressProductNormalizer::cleanDescription(
            'High quality mouse<br>Source: https://www.aliexpress.com/item/1005006422704079.html'
        );
        $paragraphCleaned = AliExpressProductNormalizer::cleanDescription(
            '<p>High quality mouse</p><p>Source: https://www.aliexpress.com/item/1005006422704079.html</p>'
        );

        $this->assertSame('High quality mouse', strip_tags($brCleaned));
        $this->assertSame('<p>High quality mouse</p>', $paragraphCleaned);
        $this->assertStringNotContainsString('Source:', $brCleaned);
        $this->assertStringNotContainsString('Source:', $paragraphCleaned);
        $this->assertStringNotContainsString('aliexpress.com/item', $brCleaned);
        $this->assertStringNotContainsString('aliexpress.com/item', $paragraphCleaned);
    }

    public function test_normalized_description_is_cleaned_before_import_storage(): void
    {
        $normalizer = new AliExpressProductNormalizer();

        $normalized = $normalizer->normalize([
            'aliexpress_ds_product_get_response' => [
                'result' => [
                    'ae_item_base_info_dto' => [
                        'product_id' => '1005001234567890',
                        'subject' => 'Example Product',
                        'detail' => '<p>Real product content</p><p>Source: https://www.aliexpress.com/item/1005006422704079.html</p>',
                        'currency_code' => 'USD',
                        'product_status_type' => 'onSelling',
                    ],
                    'ae_item_sku_info_dtos' => [
                        [
                            'sku_price' => '10.00',
                            'currency_code' => 'USD',
                            'sku_available_stock' => 1,
                        ],
                    ],
                ],
            ],
        ], 25);

        $this->assertSame('<p>Real product content</p>', $normalized['description']);
        $this->assertStringNotContainsString('Source:', $normalized['description']);
        $this->assertStringNotContainsString('aliexpress.com/item', $normalized['description']);
    }
}
