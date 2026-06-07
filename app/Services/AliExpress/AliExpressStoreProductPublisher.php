<?php

namespace App\Services\AliExpress;

use App\Models\AliExpressProduct;
use App\Models\Category;
use App\Models\Product;
use App\Services\IntegrationLogService;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class AliExpressStoreProductPublisher
{
    public function __construct(
        private readonly AliExpressPricingService $pricingService,
        private readonly AliExpressProductPolicy $policy,
        private readonly IntegrationLogService $integrationLog,
    ) {
    }

    public function publish(
        AliExpressProduct $source,
        ?int $categoryId = null,
        ?int $subCategoryId = null,
        ?int $subSubCategoryId = null,
        int $userId = 1,
        string $addedBy = 'admin',
        int $status = 1,
        int $requestStatus = 1,
    ): Product {
        $result = $this->publishWithStatus($source, $categoryId, $subCategoryId, $subSubCategoryId, $userId, $addedBy, $status, $requestStatus);

        if (($result['status'] ?? null) === 'failed') {
            throw new \RuntimeException((string) ($result['message'] ?? 'AliExpress publish failed.'));
        }

        return $result['product'];
    }

    public function publishWithStatus(
        AliExpressProduct $source,
        ?int $categoryId = null,
        ?int $subCategoryId = null,
        ?int $subSubCategoryId = null,
        int $userId = 1,
        string $addedBy = 'admin',
        int $status = 1,
        int $requestStatus = 1,
    ): array {
        $policyResult = $this->policy->evaluate($source);
        if ($policyResult['blocked']) {
            $source->update([
                'sync_status' => 'blocked',
                'block_reason' => implode(', ', $policyResult['block_reasons']),
                'warning_flags' => $policyResult['warnings'],
            ]);
            $this->integrationLog->log('aliexpress', 'product_publish_failed', 'blocked', $source->block_reason, [
                'product_id' => $source->ali_express_product_id,
                'block_reasons' => $policyResult['block_reasons'],
            ], (string) $source->ali_express_product_id);

            throw new AliExpressProductBlockedException($policyResult['block_reasons']);
        }

        $existingProduct = $this->findExistingProduct($source);
        $wasExisting = (bool) $existingProduct;
        $resolvedCategoryId = $categoryId ?: Category::query()->where('parent_id', 0)->value('id');
        $variantPayload = $this->buildVariantPayload($source);
        $unitPrice = $variantPayload['unit_price'] ?? $this->resolveUnitPrice($source);
        $stock = $variantPayload['stock'] ?? $this->resolveStock($source);
        $imageAssets = $this->prepareImageAssets($source, $existingProduct);
        $downloadedImages = $imageAssets['gallery'];
        $thumbnailName = $imageAssets['thumbnail'];
        $imageWarnings = $imageAssets['warnings'];
        $hasUsableImages = $thumbnailName !== null && (!empty($downloadedImages) || !empty($existingProduct?->images));
        $effectiveStatus = $hasUsableImages ? $status : 0;

        $imagesJson = !empty($downloadedImages)
            ? json_encode(array_map(static fn (string $name) => ['image_name' => $name, 'storage' => 'public'], $downloadedImages), JSON_UNESCAPED_SLASHES)
            : null;

        $categoryIds = array_values(array_filter([
            $resolvedCategoryId ? ['id' => $resolvedCategoryId, 'position' => 1] : null,
            $subCategoryId ? ['id' => $subCategoryId, 'position' => 2] : null,
            $subSubCategoryId ? ['id' => $subSubCategoryId, 'position' => 3] : null,
        ]));

        $slugBase = Str::slug($source->title ?: ('aliexpress-' . $source->ali_express_product_id));
        $productData = [
            'added_by' => $addedBy,
            'user_id' => $userId,
            'name' => $source->title ?: ('AliExpress Product ' . $source->ali_express_product_id),
            'slug' => $slugBase . '-' . $source->ali_express_product_id,
            'product_type' => 'physical',
            'category_ids' => !empty($categoryIds) ? json_encode($categoryIds, JSON_UNESCAPED_SLASHES) : null,
            'category_id' => $resolvedCategoryId,
            'sub_category_id' => $subCategoryId,
            'sub_sub_category_id' => $subSubCategoryId,
            'unit' => 'pc',
            'details' => $this->buildDetails($source),
            'images' => $imagesJson ?? $existingProduct?->images,
            'thumbnail' => $thumbnailName,
            'thumbnail_storage_type' => 'public',
            'colors' => json_encode([]),
            'attributes' => !empty($variantPayload['attribute_ids']) ? json_encode($variantPayload['attribute_ids']) : null,
            'choice_options' => !empty($variantPayload['choice_options']) ? json_encode($variantPayload['choice_options'], JSON_UNESCAPED_SLASHES) : null,
            'variation' => !empty($variantPayload['variations']) ? json_encode($variantPayload['variations'], JSON_UNESCAPED_SLASHES) : null,
            'variant_product' => !empty($variantPayload['variations']) ? 1 : 0,
            'published' => $effectiveStatus,
            'unit_price' => $unitPrice,
            'purchase_price' => (float) ($source->supplier_price ?? 0),
            'tax' => 0,
            'tax_type' => 'percent',
            'tax_model' => 'exclude',
            'discount' => 0,
            'discount_type' => 'flat',
            'current_stock' => $stock,
            'minimum_order_qty' => 1,
            'free_shipping' => 0,
            'status' => $effectiveStatus,
            'featured_status' => 1,
            'request_status' => $requestStatus,
            'shipping_cost' => 0,
            'multiply_qty' => 0,
            'is_shipping_cost_updated' => 0,
            'code' => 'AE-' . $source->ali_express_product_id,
            'meta_title' => $source->title,
            'meta_description' => Str::limit(strip_tags((string) AliExpressProductNormalizer::cleanDescription($source->description)), 255),
            'meta_image' => $thumbnailName,
        ];

        if ($existingProduct) {
            $existingProduct->fill($productData);
            $existingProduct->save();
            $product = $existingProduct;
        } else {
            $product = Product::query()->create($productData);
        }

        $source->update([
            'local_product_id' => $product->id,
            'variant_mappings' => $this->buildVariantMappings($source, $variantPayload),
            'block_reason' => null,
            'warning_flags' => array_values(array_unique(array_merge($policyResult['warnings'], $imageWarnings))),
        ]);

        $this->integrationLog->log('aliexpress', 'product_published', 'success', null, [
            'product_id' => $product->id,
            'status' => $wasExisting ? 'updated' : 'created',
            'warnings' => array_values(array_unique(array_merge($policyResult['warnings'], $imageWarnings))),
        ], (string) $source->ali_express_product_id);

        return [
            'status' => $wasExisting ? 'updated' : 'created',
            'product' => $product,
            'warnings' => array_values(array_unique(array_merge($policyResult['warnings'], $imageWarnings))),
            'message' => $wasExisting ? 'Existing product updated.' : 'Product created.',
        ];
    }

    private function findExistingProduct(AliExpressProduct $source): ?Product
    {
        if ($source->local_product_id) {
            $product = Product::query()->find($source->local_product_id);
            if ($product) {
                return $product;
            }
        }

        $query = Product::query();
        if (Schema::hasColumn('products', 'source') && Schema::hasColumn('products', 'external_product_id')) {
            $product = (clone $query)
                ->where('source', 'aliexpress')
                ->where('external_product_id', $source->ali_express_product_id)
                ->first();
            if ($product) {
                return $product;
            }
        }

        return Product::query()->where('code', 'AE-' . $source->ali_express_product_id)->first();
    }

    private function buildVariantPayload(AliExpressProduct $source): array
    {
        $variants = is_array($source->variants) ? $source->variants : [];
        if (empty($variants)) {
            return [];
        }

        $propertyMeta = [];
        $propertyOptions = [];
        $normalizedVariants = [];
        $priceCandidates = [];
        $stockSum = 0;

        foreach ($variants as $variant) {
            if (!is_array($variant)) {
                continue;
            }

            $properties = is_array($variant['properties'] ?? null) ? $variant['properties'] : [];
            $typeParts = [];

            foreach ($properties as $property) {
                if (!is_array($property)) {
                    continue;
                }
                $propertyId = (string) ($property['property_id'] ?? '');
                if ($propertyId === '') {
                    continue;
                }
                $propertyName = trim((string) ($property['property_name'] ?? ('Option ' . $propertyId)));
                $propertyValue = trim((string) (($property['property_value_definition_name'] ?? $property['property_value'] ?? '')));
                if ($propertyValue === '') {
                    continue;
                }

                if (!isset($propertyMeta[$propertyId])) {
                    $propertyMeta[$propertyId] = [
                        'id' => $propertyId,
                        'name' => $propertyName,
                    ];
                }

                $propertyOptions[$propertyId] = $propertyOptions[$propertyId] ?? [];
                if (!in_array($propertyValue, $propertyOptions[$propertyId], true)) {
                    $propertyOptions[$propertyId][] = $propertyValue;
                }

                $typeParts[$propertyId] = str_replace(' ', '', $propertyValue);
            }

            $type = implode('-', array_values($typeParts));
            if ($type === '') {
                $type = (string) ($variant['id'] ?? '');
            }
            if ($type === '') {
                continue;
            }

            $supplierPrice = $variant['offer_sale_price'] ?? $variant['sku_price'] ?? $variant['offer_bulk_sale_price'] ?? null;
            $pricing = is_numeric($supplierPrice)
                ? $this->pricingService->calculate((float) $supplierPrice, $source->supplier_shipping_price)
                : null;
            $price = $pricing['selling_price'] ?? null;
            $qty = $variant['stock'] ?? 0;
            $skuCode = $variant['sku_code'] ?? $variant['id'] ?? null;

            $normalizedVariants[] = [
                'type' => $type,
                'price' => is_numeric($price) ? (float) $price : 0.0,
                'sku' => is_string($skuCode) ? $skuCode : (string) $skuCode,
                'qty' => is_numeric($qty) ? (int) $qty : 0,
            ];

            if (is_numeric($price) && (float) $price > 0) {
                $priceCandidates[] = (float) $price;
            }
            if (is_numeric($qty)) {
                $stockSum += (int) $qty;
            }
        }

        $choiceOptions = [];
        $attributeIds = [];
        $index = 0;
        foreach ($propertyMeta as $propertyId => $meta) {
            $index++;
            $attributeIds[] = $index;
            $choiceOptions[] = [
                'name' => 'choice_' . $index,
                'title' => $meta['name'],
                'options' => array_values($propertyOptions[$propertyId] ?? []),
            ];
        }

        return [
            'choice_options' => $choiceOptions,
            'attribute_ids' => $attributeIds,
            'variations' => $normalizedVariants,
            'unit_price' => !empty($priceCandidates) ? min($priceCandidates) : null,
            'stock' => max(0, $stockSum),
        ];
    }

    private function prepareImageAssets(AliExpressProduct $source, ?Product $existingProduct): array
    {
        $existingGallery = $this->existingGalleryNames($existingProduct);
        $existingThumbnail = $this->existingThumbnailName($existingProduct);

        if ($existingThumbnail !== null && !empty($existingGallery)) {
            return [
                'gallery' => $existingGallery,
                'thumbnail' => $existingThumbnail,
                'warnings' => [],
            ];
        }

        if ($existingThumbnail === null && !empty($existingGallery[0]) && Storage::disk('public')->exists('product/' . $existingGallery[0])) {
            Storage::disk('public')->copy('product/' . $existingGallery[0], 'product/thumbnail/' . $existingGallery[0]);
            $existingThumbnail = $existingGallery[0];

            return [
                'gallery' => $existingGallery,
                'thumbnail' => $existingThumbnail,
                'warnings' => [],
            ];
        }

        $downloadedImages = $this->downloadImages($source->images ?? []);
        $thumbnailName = $downloadedImages[0] ?? $existingThumbnail;

        if ($thumbnailName !== null && !Storage::disk('public')->exists('product/thumbnail/' . $thumbnailName)) {
            if (Storage::disk('public')->exists('product/' . $thumbnailName)) {
                Storage::disk('public')->copy('product/' . $thumbnailName, 'product/thumbnail/' . $thumbnailName);
            } else {
                $thumbnailName = null;
            }
        }

        $gallery = !empty($downloadedImages) ? $downloadedImages : $existingGallery;
        $warnings = [];
        if ($thumbnailName === null || empty($gallery)) {
            $warnings[] = 'image_download_failed';
        }

        return [
            'gallery' => $gallery,
            'thumbnail' => $thumbnailName,
            'warnings' => $warnings,
        ];
    }

    private function resolveUnitPrice(AliExpressProduct $source): float
    {
        if (is_numeric($source->supplier_price) && (float) $source->supplier_price > 0) {
            return $this->pricingService->calculate((float) $source->supplier_price, $source->supplier_shipping_price)['selling_price'];
        }

        if (is_numeric($source->selling_price) && (float) $source->selling_price > 0) {
            return (float) $source->selling_price;
        }

        foreach ((array) ($source->variants ?? []) as $variant) {
            if (!is_array($variant)) {
                continue;
            }
            $price = $variant['offer_sale_price'] ?? $variant['sku_price'] ?? $variant['offer_bulk_sale_price'] ?? null;
            if (is_numeric($price) && (float) $price > 0) {
                return $this->pricingService->calculate((float) $price, $source->supplier_shipping_price)['selling_price'];
            }
        }

        return 0.0;
    }

    private function resolveStock(AliExpressProduct $source): int
    {
        if (is_numeric($source->stock) && (int) $source->stock > 0) {
            return (int) $source->stock;
        }

        $sum = 0;
        foreach ((array) ($source->variants ?? []) as $variant) {
            if (!is_array($variant)) {
                continue;
            }
            $qty = $variant['stock'] ?? 0;
            if (is_numeric($qty)) {
                $sum += (int) $qty;
            }
        }

        return max(0, $sum);
    }

    private function buildDetails(AliExpressProduct $source): string
    {
        return trim((string) AliExpressProductNormalizer::cleanDescription($source->description));
    }

    private function downloadImages(array $imageUrls): array
    {
        $saved = [];
        $urls = array_slice(array_values(array_unique(array_filter($imageUrls, static fn ($url) => is_string($url) && $url !== ''))), 0, 8);

        foreach ($urls as $index => $url) {
            try {
                $url = trim(str_starts_with($url, '//') ? 'https:' . $url : $url);
                if (!filter_var($url, FILTER_VALIDATE_URL)) {
                    continue;
                }

                $response = Http::timeout(30)
                    ->retry(2, 300)
                    ->withoutVerifying()
                    ->withHeaders([
                        'User-Agent' => 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124.0.0.0 Safari/537.36',
                        'Referer' => 'https://www.aliexpress.com/',
                        'Accept' => 'image/avif,image/webp,image/apng,image/*,*/*;q=0.8',
                    ])
                    ->get($url);
                if (!$response->successful()) {
                    continue;
                }

                $extension = $this->guessImageExtension($response->header('Content-Type'), $url);
                $fileName = 'ae-' . now()->format('YmdHis') . '-' . $index . '-' . Str::random(8) . '.' . $extension;
                Storage::disk('public')->put('product/' . $fileName, $response->body());
                if ($index === 0) {
                    Storage::disk('public')->put('product/thumbnail/' . $fileName, $response->body());
                }
                $saved[] = $fileName;
            } catch (\Throwable) {
                continue;
            }
        }

        return $saved;
    }

    private function guessImageExtension(?string $contentType, string $url): string
    {
        $contentType = strtolower((string) $contentType);

        if (str_contains($contentType, 'png')) {
            return 'png';
        }
        if (str_contains($contentType, 'webp')) {
            return 'webp';
        }
        if (str_contains($contentType, 'gif')) {
            return 'gif';
        }

        $path = parse_url($url, PHP_URL_PATH);
        $extension = strtolower(pathinfo((string) $path, PATHINFO_EXTENSION));

        return in_array($extension, ['jpg', 'jpeg', 'png', 'webp', 'gif'], true) ? $extension : 'jpg';
    }

    private function existingGalleryNames(?Product $existingProduct): array
    {
        if (!$existingProduct?->images) {
            return [];
        }

        $images = json_decode((string) $existingProduct->images, true);
        if (!is_array($images)) {
            return [];
        }

        return collect($images)
            ->map(function ($image) {
                if (is_array($image)) {
                    return $image['image_name'] ?? null;
                }

                return is_string($image) ? $image : null;
            })
            ->filter(fn ($image) => is_string($image) && $image !== '' && Storage::disk('public')->exists('product/' . $image))
            ->values()
            ->all();
    }

    private function existingThumbnailName(?Product $existingProduct): ?string
    {
        $thumbnail = (string) ($existingProduct?->thumbnail ?? '');
        if ($thumbnail === '') {
            return null;
        }

        return Storage::disk('public')->exists('product/thumbnail/' . $thumbnail) ? $thumbnail : null;
    }

    private function buildVariantMappings(AliExpressProduct $source, array $variantPayload): array
    {
        $mappings = [];
        foreach ((array) ($source->variants ?? []) as $variant) {
            if (!is_array($variant)) {
                continue;
            }

            $externalSku = (string) ($variant['sku_code'] ?? $variant['id'] ?? '');
            if ($externalSku === '') {
                continue;
            }

            $local = collect($variantPayload['variations'] ?? [])->firstWhere('sku', $externalSku);
            $mappings[] = [
                'external_variant_id' => $variant['id'] ?? null,
                'external_sku' => $externalSku,
                'local_sku' => $local['sku'] ?? $externalSku,
                'local_type' => $local['type'] ?? null,
            ];
        }

        return $mappings;
    }
}
