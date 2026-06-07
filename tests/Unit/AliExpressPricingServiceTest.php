<?php

namespace Tests\Unit;

use App\Services\AliExpress\AliExpressPricingService;
use Tests\TestCase;

class AliExpressPricingServiceTest extends TestCase
{
    public function test_percentage_markup_with_minimum_profit_and_shipping(): void
    {
        $result = (new AliExpressPricingService())->calculate(10, 3, [
            'markup_type' => 'percentage',
            'markup_value' => 20,
            'minimum_profit' => 5,
            'include_shipping_cost' => true,
            'rounding_rule' => 'none',
            'currency' => 'USD',
        ]);

        $this->assertSame(13.0, $result['cost']);
        $this->assertSame(18.0, $result['selling_price']);
        $this->assertSame(5.0, $result['profit']);
    }

    public function test_fixed_markup_rounds_to_99(): void
    {
        $result = (new AliExpressPricingService())->calculate(10, null, [
            'markup_type' => 'fixed',
            'markup_value' => 4,
            'minimum_profit' => 0,
            'include_shipping_cost' => false,
            'rounding_rule' => 'nearest_99',
            'currency' => 'USD',
        ]);

        $this->assertSame(14.99, $result['selling_price']);
    }
}
