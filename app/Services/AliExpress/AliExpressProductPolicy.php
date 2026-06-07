<?php

namespace App\Services\AliExpress;

use App\Models\AliExpressProduct;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class AliExpressProductPolicy
{
    public function evaluate(AliExpressProduct|array $product): array
    {
        $data = $product instanceof AliExpressProduct ? $product->toArray() : $product;
        $blockReasons = [];
        $warnings = [];

        $price = $data['selling_price'] ?? $data['supplier_price'] ?? null;
        if (!is_numeric($price)) {
            $blockReasons[] = 'missing_price';
        } elseif ((float) $price <= 0) {
            $blockReasons[] = 'zero_price';
        }

        if (empty($data['images']) || !is_array($data['images'])) {
            $blockReasons[] = 'missing_images';
        }

        $variantsRequired = (bool) config('aliexpress.policy.require_variants', false);
        if ($variantsRequired && empty($data['variants'])) {
            $blockReasons[] = 'missing_variants';
        }

        if (array_key_exists('is_active', $data) && !$data['is_active']) {
            $blockReasons[] = 'unavailable_product_status';
        }
        if (array_key_exists('is_available', $data) && !$data['is_available']) {
            $blockReasons[] = 'supplier_unavailable';
        }

        $text = Str::lower(trim(($data['title'] ?? '') . ' ' . strip_tags((string) ($data['description'] ?? ''))));
        foreach ((array) config('aliexpress.policy.blocked_keywords', []) as $keyword) {
            if ($keyword !== '' && str_contains($text, Str::lower((string) $keyword))) {
                $blockReasons[] = 'blocked_keyword:' . $keyword;
            }
        }
        foreach ((array) config('aliexpress.policy.suspicious_brand_terms', []) as $term) {
            if ($term !== '' && str_contains($text, Str::lower((string) $term))) {
                $warnings[] = 'suspicious_brand_term:' . $term;
            }
        }

        $rating = Arr::get($data, 'raw_payload.rating') ?? Arr::get($data, 'raw_payload.aliexpress_ds_product_get_response.result.evaluation_rating');
        $minimumRating = (float) config('aliexpress.policy.minimum_rating', 0);
        if ($minimumRating > 0 && is_numeric($rating) && (float) $rating < $minimumRating) {
            $blockReasons[] = 'low_rating';
        }

        $orderCount = Arr::get($data, 'raw_payload.order_count') ?? Arr::get($data, 'raw_payload.aliexpress_ds_product_get_response.result.order_count');
        $minimumOrderCount = (int) config('aliexpress.policy.minimum_order_count', 0);
        if ($minimumOrderCount > 0 && is_numeric($orderCount) && (int) $orderCount < $minimumOrderCount) {
            $warnings[] = 'low_order_count';
        }

        return [
            'allowed' => empty($blockReasons),
            'blocked' => !empty($blockReasons),
            'block_reasons' => array_values(array_unique($blockReasons)),
            'warnings' => array_values(array_unique($warnings)),
        ];
    }
}
