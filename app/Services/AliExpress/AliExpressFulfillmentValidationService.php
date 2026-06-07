<?php

namespace App\Services\AliExpress;

use App\Models\Order;

class AliExpressFulfillmentValidationService
{
    public function validate(Order $order, array $fulfillment): array
    {
        $warnings = [];
        $address = $fulfillment['address'] ?? [];
        $customer = $fulfillment['customer'] ?? [];

        foreach ([
            'customer_name_missing' => $customer['name'] ?? null,
            'customer_phone_missing' => $customer['phone'] ?? null,
            'country_missing' => $address['country'] ?? null,
            'city_missing' => $address['city'] ?? null,
            'address_missing' => $address['address_line_1'] ?? null,
        ] as $warning => $value) {
            if (trim((string) $value) === '') {
                $warnings[] = $warning;
            }
        }

        $country = strtoupper((string) ($address['country'] ?? ''));
        if (in_array($country, ['US', 'USA', 'CA', 'CANADA'], true) && trim((string) ($address['zip'] ?? '')) === '') {
            $warnings[] = 'postal_code_missing';
        }

        foreach ($fulfillment['items'] ?? [] as $index => $item) {
            if (empty($item['ali_express_product_id'])) {
                $warnings[] = 'item_' . ($index + 1) . '_missing_aliexpress_mapping';
            }
            if (($item['supplier_status'] ?? null) === 'unavailable') {
                $warnings[] = 'item_' . ($index + 1) . '_supplier_unavailable';
            }
        }

        return array_values(array_unique($warnings));
    }
}
