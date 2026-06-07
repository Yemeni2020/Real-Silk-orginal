<?php

namespace Tests\Unit;

use App\Models\Order;
use App\Services\AliExpress\AliExpressFulfillmentValidationService;
use Tests\TestCase;

class AliExpressFulfillmentValidationServiceTest extends TestCase
{
    public function test_reports_missing_shipping_and_mapping_warnings(): void
    {
        $warnings = (new AliExpressFulfillmentValidationService())->validate(new Order(), [
            'customer' => ['name' => '', 'phone' => ''],
            'address' => ['country' => 'US', 'city' => '', 'zip' => '', 'address_line_1' => ''],
            'items' => [
                ['ali_express_product_id' => null],
            ],
        ]);

        $this->assertContains('customer_name_missing', $warnings);
        $this->assertContains('customer_phone_missing', $warnings);
        $this->assertContains('postal_code_missing', $warnings);
        $this->assertContains('item_1_missing_aliexpress_mapping', $warnings);
    }
}
