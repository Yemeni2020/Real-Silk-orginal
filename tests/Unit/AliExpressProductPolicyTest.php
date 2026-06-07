<?php

namespace Tests\Unit;

use App\Services\AliExpress\AliExpressProductPolicy;
use Tests\TestCase;

class AliExpressProductPolicyTest extends TestCase
{
    public function test_blocks_missing_price_and_images(): void
    {
        $result = (new AliExpressProductPolicy())->evaluate([
            'title' => 'Simple product',
            'supplier_price' => null,
            'images' => [],
            'variants' => [],
            'is_active' => true,
            'is_available' => true,
        ]);

        $this->assertTrue($result['blocked']);
        $this->assertContains('missing_price', $result['block_reasons']);
        $this->assertContains('missing_images', $result['block_reasons']);
    }

    public function test_warns_for_suspicious_brand_terms(): void
    {
        config(['aliexpress.policy.suspicious_brand_terms' => ['BrandX']]);

        $result = (new AliExpressProductPolicy())->evaluate([
            'title' => 'BrandX compatible case',
            'supplier_price' => 10,
            'images' => ['https://example.test/a.jpg'],
            'is_active' => true,
            'is_available' => true,
        ]);

        $this->assertFalse($result['blocked']);
        $this->assertContains('suspicious_brand_term:BrandX', $result['warnings']);
    }
}
