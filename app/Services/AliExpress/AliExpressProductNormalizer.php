<?php

namespace App\Services\AliExpress;

use Illuminate\Support\Arr;
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

        return [
            'ali_express_product_id' => $productId,
            'title' => Arr::get($baseInfo, 'subject'),
            'description' => Arr::get($baseInfo, 'detail'),
            'supplier_price' => $supplierPrice,
            'selling_price' => $supplierPrice !== null ? round($supplierPrice * (1 + ($margin / 100)), 2) : null,
            'margin' => $margin,
            'stock' => $stock,
            'currency' => $currency,
            'images' => $images,
            'variants' => $skus,
            'supplier_url' => $productId !== '' ? 'https://www.aliexpress.com/item/' . $productId . '.html' : null,
            'is_active' => Arr::get($baseInfo, 'product_status_type') === 'onSelling',
            'raw_payload' => $response,
        ];
    }

    private function normalizeSkus(mixed $skus): array
    {
        return collect(Arr::wrap($skus))
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
                ->map(fn (string $image) => trim($image))
                ->filter()
                ->values()
                ->all();
        }

        return collect(Arr::wrap($images))
            ->filter(fn ($image) => is_string($image) && $image !== '')
            ->values()
            ->all();
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

    private function toFloat(mixed $value): ?float
    {
        return is_numeric($value) ? (float) $value : null;
    }

    private function toInt(mixed $value): ?int
    {
        return is_numeric($value) ? (int) $value : null;
    }
}
