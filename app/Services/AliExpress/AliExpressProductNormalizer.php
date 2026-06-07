<?php

namespace App\Services\AliExpress;

use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class AliExpressProductNormalizer
{
    public function normalize(array $response, float $margin): array
    {
        $payload = Arr::get($response, 'aliexpress_ds_product_get_response.result', []);
        $baseInfo = Arr::get($payload, 'ae_item_base_info_dto', []);
        $skus = $this->normalizeSkus(Arr::get($payload, 'ae_item_sku_info_dtos', []));
        $images = $this->normalizeImages(Arr::get($payload, 'ae_multimedia_info_dto.image_urls'));
        $supplierPrice = $this->resolveSupplierPrice($skus);
        $stock = $this->resolveStock($skus);
        $productId = (string) (Arr::get($baseInfo, 'product_id') ?: Arr::get($payload, 'product_id'));
        $currency = $this->resolveCurrency($baseInfo, $skus);
        $shippingPrice = $this->resolveShippingPrice($payload);
        $pricing = app(AliExpressPricingService::class)->calculate(
            (float) ($supplierPrice ?? 0),
            $shippingPrice,
            [
                'markup_type' => 'percentage',
                'markup_value' => $margin,
                'minimum_profit' => (float) config('aliexpress.pricing.minimum_profit', 0),
                'include_shipping_cost' => (bool) config('aliexpress.pricing.include_shipping_cost', false),
                'rounding_rule' => (string) config('aliexpress.pricing.rounding_rule', 'none'),
                'currency' => $currency ?: config('aliexpress.default_currency'),
            ]
        );
        $isAvailable = Arr::get($baseInfo, 'product_status_type') === 'onSelling' && $productId !== '' && ($supplierPrice === null || $supplierPrice > 0);

        return [
            'ali_express_product_id' => $productId,
            'title' => Arr::get($baseInfo, 'subject'),
            'description' => self::cleanDescription(Arr::get($baseInfo, 'detail')),
            'supplier_price' => $supplierPrice,
            'supplier_shipping_price' => $shippingPrice,
            'selling_price' => $supplierPrice !== null ? $pricing['selling_price'] : null,
            'margin' => $margin,
            'stock' => $stock,
            'currency' => $currency,
            'supplier_currency' => $currency,
            'images' => $images,
            'variants' => $skus,
            'supplier_url' => $productId !== '' ? 'https://www.aliexpress.com/item/' . $productId . '.html' : null,
            'supplier_product_url' => $productId !== '' ? 'https://www.aliexpress.com/item/' . $productId . '.html' : null,
            'is_active' => Arr::get($baseInfo, 'product_status_type') === 'onSelling',
            'is_available' => $isAvailable,
            'supplier_stock_status' => $stock > 0 ? 'in_stock' : 'out_of_stock',
            'sync_status' => $isAvailable ? 'synced' : 'unavailable',
            'sync_error' => null,
            'last_synced_at' => now(),
            'source_updated_at' => $this->resolveSourceUpdatedAt($baseInfo),
            'raw_payload' => $response,
        ];
    }

    private function normalizeSkus(mixed $skus): array
    {
        $list = Arr::get(Arr::wrap($skus), 'ae_item_sku_info_d_t_o');
        if (!is_array($list)) {
            $list = Arr::wrap($skus);
        }

        return collect($list)
            ->filter(fn ($sku) => is_array($sku))
            ->map(function (array $sku) {
                $properties = Arr::wrap($sku['aeop_s_k_u_propertys'] ?? Arr::get($sku, 'ae_sku_property_dtos.ae_sku_property_d_t_o', $sku['ae_sku_property_dtos'] ?? []));

                return [
                    'id' => $sku['id'] ?? null,
                    'sku_code' => $sku['sku_code'] ?? null,
                    'sku_price' => $this->toFloat($sku['sku_price'] ?? null),
                    'offer_sale_price' => $this->toFloat($sku['offer_sale_price'] ?? null),
                    'offer_bulk_sale_price' => $this->toFloat($sku['offer_bulk_sale_price'] ?? null),
                    'currency_code' => $sku['currency_code'] ?? null,
                    'stock' => $this->toInt($sku['sku_available_stock'] ?? $sku['ipm_sku_stock'] ?? null),
                    'properties' => collect($properties)
                        ->filter(fn ($property) => is_array($property))
                        ->map(fn (array $property) => [
                            'property_id' => $property['sku_property_id'] ?? null,
                            'property_name' => $property['sku_property_name'] ?? null,
                            'property_value' => $property['sku_property_value'] ?? null,
                            'property_value_id' => $property['property_value_id'] ?? null,
                            'sku_image' => $property['sku_image'] ?? null,
                        ])
                        ->values()
                        ->all(),
                ];
            })
            ->values()
            ->all();
    }

    private function normalizeImages(mixed $images): array
    {
        if (is_string($images)) {
            return collect(explode(';', $images))
                ->map(fn (string $image) => $this->normalizeImageUrl($image))
                ->filter()
                ->values()
                ->all();
        }

        return collect(Arr::wrap($images))
            ->map(fn ($image) => is_string($image) ? $this->normalizeImageUrl($image) : null)
            ->filter()
            ->values()
            ->all();
    }

    private function normalizeImageUrl(string $image): ?string
    {
        $image = trim($image);
        if ($image === '') {
            return null;
        }

        if (str_starts_with($image, '//')) {
            $image = 'https:' . $image;
        }

        if (!filter_var($image, FILTER_VALIDATE_URL)) {
            return null;
        }

        return $image;
    }

    public static function cleanDescription(mixed $description): ?string
    {
        if ($description === null) {
            return null;
        }

        $description = trim((string) $description);
        if ($description === '') {
            return '';
        }

        $sourcePattern = 'Source:\s*(?:<a\b[^>]*>\s*)?https?:\/\/(?:www\.)?aliexpress\.com\/item\/[^<\s]+(?:\s*<\/a>)?';

        $description = preg_replace('/<p\b[^>]*>\s*' . $sourcePattern . '\s*<\/p>\s*/iu', '', $description) ?? $description;
        $description = preg_replace('/(?:^|\s*<br\s*\/?>\s*)' . $sourcePattern . '\s*(?=<br\s*\/?>|$)/iu', '', $description) ?? $description;

        $lines = preg_split('/\R/u', $description);
        if ($lines === false) {
            return trim($description);
        }

        $lines = array_filter($lines, static function (string $line) use ($sourcePattern): bool {
            $plainLine = trim(strip_tags($line));

            return preg_match('/^Source:\s*https?:\/\/(?:www\.)?aliexpress\.com\/item\/\S+$/iu', $plainLine) !== 1
                && preg_match('/^\s*' . $sourcePattern . '\s*$/iu', trim($line)) !== 1;
        });

        return trim(implode("\n", $lines));
    }

    private function resolveSupplierPrice(array $skus): ?float
    {
        $prices = collect($skus)
            ->flatMap(fn (array $sku) => [$sku['offer_sale_price'] ?? null, $sku['sku_price'] ?? null, $sku['offer_bulk_sale_price'] ?? null])
            ->filter(fn ($price) => $price !== null)
            ->map(fn ($price) => (float) $price)
            ->sort()
            ->values();

        return $prices->first();
    }

    private function resolveStock(array $skus): int
    {
        return (int) collect($skus)->sum(fn (array $sku) => $sku['stock'] ?? 0);
    }

    private function resolveCurrency(array $baseInfo, array $skus): ?string
    {
        $currency = Arr::get($baseInfo, 'currency_code') ?: Arr::get($skus, '0.currency_code');

        return is_string($currency) && Str::length($currency) <= 10 ? $currency : null;
    }

    private function resolveShippingPrice(array $payload): ?float
    {
        foreach ([
            'shipping_price',
            'freight_amount.value',
            'logistics_info.shipping_fee',
            'ae_item_base_info_dto.shipping_price',
        ] as $key) {
            $value = Arr::get($payload, $key);
            if (is_numeric($value)) {
                return (float) $value;
            }
        }

        return null;
    }

    private function resolveSourceUpdatedAt(array $baseInfo): ?string
    {
        $value = Arr::get($baseInfo, 'gmt_modified') ?: Arr::get($baseInfo, 'last_update_time');

        if (!$value) {
            return null;
        }

        try {
            if (is_numeric($value)) {
                return Carbon::createFromTimestampMs((int) $value)->toDateTimeString();
            }

            return Carbon::parse((string) $value)->toDateTimeString();
        } catch (\Throwable) {
            return null;
        }
    }

    private function toFloat(mixed $value): ?float
    {
        return is_numeric($value) ? (float) $value : null;
    }

    private function toInt(mixed $value): ?int
    {
        return is_numeric($value) ? (int) $value : null;
    }
}
