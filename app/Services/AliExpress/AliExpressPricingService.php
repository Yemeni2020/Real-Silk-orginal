<?php

namespace App\Services\AliExpress;

class AliExpressPricingService
{
    public function calculate(
        float $supplierPrice,
        ?float $shippingPrice = null,
        ?array $rules = null,
    ): array {
        $rules = $rules ?? $this->rulesFromConfig();
        $shipping = (bool) ($rules['include_shipping_cost'] ?? false) ? max(0, (float) ($shippingPrice ?? 0)) : 0.0;
        $cost = max(0, $supplierPrice) + $shipping;
        $markupType = (string) ($rules['markup_type'] ?? 'percentage');
        $markupValue = (float) ($rules['markup_value'] ?? 0);

        $markup = $markupType === 'fixed'
            ? $markupValue
            : $cost * ($markupValue / 100);

        $minimumProfit = max(0, (float) ($rules['minimum_profit'] ?? 0));
        $price = $cost + max($markup, $minimumProfit);
        $price = $this->applyRounding($price, (string) ($rules['rounding_rule'] ?? 'none'));

        $compareAt = null;
        $compareAtMultiplier = (float) ($rules['compare_at_multiplier'] ?? 0);
        if ($compareAtMultiplier > 1) {
            $compareAt = $this->applyRounding($price * $compareAtMultiplier, (string) ($rules['rounding_rule'] ?? 'none'));
        }

        return [
            'supplier_price' => round($supplierPrice, 2),
            'shipping_price' => round($shippingPrice ?? 0, 2),
            'cost' => round($cost, 2),
            'selling_price' => round($price, 2),
            'profit' => round($price - $cost, 2),
            'compare_at_price' => $compareAt,
            'currency' => $rules['currency'] ?? config('aliexpress.default_currency'),
        ];
    }

    public function rulesFromConfig(): array
    {
        return [
            'markup_type' => (string) config('aliexpress.pricing.markup_type', 'percentage'),
            'markup_value' => (float) config('aliexpress.pricing.markup_value', config('aliexpress.default_margin', 0)),
            'minimum_profit' => (float) config('aliexpress.pricing.minimum_profit', 0),
            'include_shipping_cost' => (bool) config('aliexpress.pricing.include_shipping_cost', false),
            'rounding_rule' => (string) config('aliexpress.pricing.rounding_rule', 'none'),
            'currency' => (string) config('aliexpress.pricing.currency', config('aliexpress.default_currency', 'USD')),
            'compare_at_multiplier' => (float) config('aliexpress.pricing.compare_at_multiplier', 0),
        ];
    }

    private function applyRounding(float $price, string $rule): float
    {
        return match ($rule) {
            'nearest_99' => floor($price) + 0.99,
            'up_99' => ceil($price) - 0.01,
            'up_integer' => ceil($price),
            default => round($price, 2),
        };
    }
}
