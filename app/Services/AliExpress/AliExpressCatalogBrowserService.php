<?php

namespace App\Services\AliExpress;

use App\Models\AliExpressProductPreview;
use App\Services\IntegrationLogService;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class AliExpressCatalogBrowserService
{
    public function __construct(
        private readonly AliExpressClient $client,
        private readonly AliExpressTokenStore $tokenStore,
        private readonly AliExpressProductPreviewService $previewService,
        private readonly IntegrationLogService $integrationLog,
    ) {
    }

    public function search(array $filters): array
    {
        $filters = $this->normalizeFilters($filters);
        if (($filters['keyword'] ?? null) === null && ($filters['category_id'] ?? null) === null) {
            return $this->emptyResult('Search by keyword or choose a category to browse AliExpress products.', $filters);
        }

        $cacheKey = 'aliexpress_catalog_search:' . md5(json_encode($filters));

        return Cache::remember($cacheKey, now()->addSeconds((int) config('aliexpress.catalog.cache_ttl', 900)), function () use ($filters) {
            $this->integrationLog->log('aliexpress', 'catalog_search_started', 'processing', null, [
                'filters' => $filters,
            ]);

            try {
                $accessToken = $this->tokenStore->getValidAccessToken($this->client);
                $response = $this->client->searchProducts(array_merge($filters, ['access_token' => $accessToken]));
            } catch (\Throwable $exception) {
                $message = str_contains(strtolower($exception->getMessage()), 'reconnect')
                    ? 'AliExpress reconnect is required before catalog search can run.'
                    : 'AliExpress is disconnected. Reconnect AliExpress or import by product URL or ID.';

                $this->integrationLog->log('aliexpress', 'catalog_search_failed', 'failed', $message, [
                    'filters' => $filters,
                ]);

                return [
                    'success' => false,
                    'message' => $message,
                    'items' => [],
                    'meta' => $this->metaFromFilters($filters),
                    'warnings' => [$exception->getMessage()],
                    'unsupported' => false,
                ];
            }

            if (!($response['success'] ?? false)) {
                $this->integrationLog->log('aliexpress', 'catalog_search_failed', 'failed', $response['message'] ?? null, [
                    'filters' => $filters,
                    'warnings' => $response['warnings'] ?? [],
                ]);

                return [
                    'success' => false,
                    'message' => $response['message'] ?? 'AliExpress catalog search failed.',
                    'items' => [],
                    'meta' => $this->metaFromFilters($filters),
                    'warnings' => $response['warnings'] ?? [],
                    'unsupported' => true,
                ];
            }

            $items = $this->normalizeSearchResults((array) ($response['data'] ?? []));
            $meta = $this->normalizeMeta((array) ($response['data'] ?? []), $filters, count($items));

            $this->integrationLog->log('aliexpress', 'catalog_search_finished', 'success', null, [
                'filters' => $filters,
                'count' => count($items),
                'meta' => $meta,
            ]);

            return [
                'success' => true,
                'message' => null,
                'items' => $items,
                'meta' => $meta,
                'warnings' => $response['warnings'] ?? [],
                'unsupported' => false,
            ];
        });
    }

    public function categories(?string $parentId = null): array
    {
        $cacheKey = 'aliexpress_catalog_categories:' . ($parentId ?: 'root');

        return Cache::remember($cacheKey, now()->addSeconds((int) config('aliexpress.catalog.cache_ttl', 900)), function () use ($parentId) {
            try {
                $accessToken = $this->tokenStore->getValidAccessToken($this->client);
                $response = $this->client->getCategories($parentId, $accessToken);
            } catch (\Throwable $exception) {
                return [
                    'success' => true,
                    'message' => 'AliExpress category browsing is not available for this API account.',
                    'items' => $this->fallbackCategories(),
                    'fallback' => true,
                    'warnings' => [$exception->getMessage()],
                ];
            }

            if (!($response['success'] ?? false)) {
                return [
                    'success' => true,
                    'message' => $response['message'] ?? 'AliExpress category browsing is not available for this API account.',
                    'items' => $this->fallbackCategories(),
                    'fallback' => true,
                    'warnings' => $response['warnings'] ?? [],
                ];
            }

            $items = $this->normalizeCategories((array) ($response['data'] ?? []));
            if (empty($items)) {
                return [
                    'success' => true,
                    'message' => 'AliExpress category API returned no categories, so default categories are shown.',
                    'items' => $this->fallbackCategories(),
                    'fallback' => true,
                    'warnings' => [],
                ];
            }

            return [
                'success' => true,
                'message' => null,
                'items' => $items,
                'fallback' => false,
                'warnings' => $response['warnings'] ?? [],
            ];
        });
    }

    public function createPreviewFromProductId(string $productId, ?int $adminId = null): AliExpressProductPreview
    {
        $preview = $this->previewService->createPreview($productId, null, $adminId);
        $this->integrationLog->log('aliexpress', 'catalog_preview_created', $preview->policy_status, null, [
            'preview_id' => $preview->id,
            'product_id' => $productId,
        ], $productId, $adminId);

        return $preview;
    }

    public function importFromCatalogResult(string $productId, ?int $adminId = null): array
    {
        $this->integrationLog->log('aliexpress', 'catalog_import_started', 'processing', null, [
            'product_id' => $productId,
        ], $productId, $adminId);

        $preview = $this->createPreviewFromProductId($productId, $adminId);
        $product = $this->previewService->importFromPreview($preview, $adminId);

        return ['preview' => $preview->fresh(), 'product' => $product];
    }

    public function publishFromCatalogResult(string $productId, ?int $adminId = null): array
    {
        $this->integrationLog->log('aliexpress', 'catalog_publish_started', 'processing', null, [
            'product_id' => $productId,
        ], $productId, $adminId);

        $preview = $this->createPreviewFromProductId($productId, $adminId);
        try {
            $result = $this->previewService->publishFromPreview($preview, false, $adminId);
        } catch (AliExpressProductBlockedException $exception) {
            throw new AliExpressCatalogProductBlockedException($preview->fresh(), $exception->reasons);
        }

        return array_merge($result, ['preview' => $preview->fresh()]);
    }

    public function normalizeSearchResults(array $response): array
    {
        $list = $this->extractResultList($response);

        return collect($list)
            ->filter(fn ($item) => is_array($item))
            ->map(fn (array $item) => $this->normalizeSearchResult($item))
            ->filter(fn (array $item) => $item['product_id'] !== '')
            ->values()
            ->all();
    }

    private function normalizeSearchResult(array $item): array
    {
        $productId = (string) (
            Arr::get($item, 'product_id')
            ?? Arr::get($item, 'item_id')
            ?? Arr::get($item, 'itemId')
            ?? Arr::get($item, 'productId')
            ?? ''
        );
        $image = Arr::get($item, 'product_main_image_url')
            ?? Arr::get($item, 'itemMainPic')
            ?? Arr::get($item, 'image_url')
            ?? Arr::get($item, 'product_small_image_urls.0')
            ?? Arr::get($item, 'image')
            ?? null;

        return [
            'product_id' => $productId,
            'title' => (string) (Arr::get($item, 'product_title') ?? Arr::get($item, 'title') ?? Arr::get($item, 'subject') ?? ''),
            'image' => is_string($image) && str_starts_with($image, '//') ? 'https:' . $image : $image,
            'price' => $this->toFloat(Arr::get($item, 'target_sale_price') ?? Arr::get($item, 'targetSalePrice') ?? Arr::get($item, 'sale_price') ?? Arr::get($item, 'salePrice') ?? Arr::get($item, 'price')),
            'currency' => (string) (Arr::get($item, 'target_sale_price_currency') ?? Arr::get($item, 'targetSalePriceCurrency') ?? Arr::get($item, 'targetOriginalPriceCurrency') ?? Arr::get($item, 'currency') ?? config('aliexpress.default_currency')),
            'rating' => $this->normalizeRating(Arr::get($item, 'score') ?? Arr::get($item, 'evaluate_rate') ?? Arr::get($item, 'evaluateRate') ?? Arr::get($item, 'rating')),
            'orders' => $this->toInt(Arr::get($item, 'sale_count') ?? Arr::get($item, 'saleCount') ?? Arr::get($item, 'orders') ?? Arr::get($item, 'order_count')),
            'category_id' => Arr::get($item, 'first_level_category_id') ?? Arr::get($item, 'category_id') ?? Arr::get($item, 'cateId'),
            'category_name' => Arr::get($item, 'first_level_category_name') ?? Arr::get($item, 'category_name'),
            'shipping_price' => $this->toFloat(Arr::get($item, 'shipping_price') ?? Arr::get($item, 'freight_amount.value')),
            'delivery_estimate' => Arr::get($item, 'delivery_estimate') ?? Arr::get($item, 'delivery_time'),
            'product_url' => $productId !== '' ? 'https://www.aliexpress.com/item/' . $productId . '.html' : (Arr::get($item, 'product_detail_url') ?? Arr::get($item, 'itemUrl') ?? null),
            'is_available' => (bool) (Arr::get($item, 'is_available') ?? true),
            'warnings' => $this->resultWarnings($item, $productId, $image),
            'raw' => $item,
        ];
    }

    private function normalizeCategories(array $response): array
    {
        $list = Arr::get($response, 'aliexpress_ds_category_get_response.result.categories.category')
            ?? Arr::get($response, 'aliexpress_ds_category_get_response.result')
            ?? Arr::get($response, 'result.categories')
            ?? Arr::get($response, 'result')
            ?? [];

        return collect(Arr::wrap($list))
            ->filter(fn ($item) => is_array($item))
            ->map(fn (array $item) => [
                'id' => (string) (Arr::get($item, 'category_id') ?? Arr::get($item, 'id') ?? ''),
                'parent_id' => Arr::get($item, 'parent_category_id') ?? Arr::get($item, 'parent_id'),
                'name' => (string) (Arr::get($item, 'category_name') ?? Arr::get($item, 'name') ?? ''),
                'is_leaf' => (bool) (Arr::get($item, 'is_leaf') ?? false),
                'raw' => $item,
            ])
            ->filter(fn (array $item) => $item['id'] !== '' && $item['name'] !== '')
            ->values()
            ->all();
    }

    private function extractResultList(array $response): array
    {
        foreach ([
            'aliexpress_ds_text_search_response.result.products.product',
            'aliexpress_ds_text_search_response.result.products',
            'aliexpress_ds_text_search_response.result.items.item',
            'aliexpress_ds_text_search_response.result.items',
            'aliexpress_ds_text_search_response.data.products.selection_search_product',
            'aliexpress_ds_text_search_response.data.products',
            'aliexpress_ds_text_search_response.data.items.selection_search_product',
            'aliexpress_ds_text_search_response.data.items',
            'result.products.product',
            'result.products',
            'result.items.item',
            'result.items',
            'products.product',
            'products',
            'items.item',
            'items',
        ] as $path) {
            $value = Arr::get($response, $path);
            if (is_array($value)) {
                return Arr::wrap($value);
            }
        }

        return [];
    }

    private function normalizeMeta(array $response, array $filters, int $count): array
    {
        $total = $this->toInt(Arr::get($response, 'aliexpress_ds_text_search_response.result.total_record_count') ?? Arr::get($response, 'aliexpress_ds_text_search_response.data.totalCount') ?? Arr::get($response, 'result.total_record_count') ?? Arr::get($response, 'data.totalCount') ?? Arr::get($response, 'total_results'));
        $perPage = (int) $filters['per_page'];

        return array_merge($this->metaFromFilters($filters), [
            'total_results' => $total,
            'total_pages' => $total && $perPage > 0 ? (int) ceil($total / $perPage) : null,
            'count' => $count,
        ]);
    }

    private function metaFromFilters(array $filters): array
    {
        return [
            'current_page' => (int) ($filters['page'] ?? 1),
            'per_page' => (int) ($filters['per_page'] ?? 20),
            'total_results' => null,
            'total_pages' => null,
        ];
    }

    private function normalizeFilters(array $filters): array
    {
        return [
            'keyword' => filled($filters['keyword'] ?? null) ? trim((string) $filters['keyword']) : null,
            'category_id' => filled($filters['category_id'] ?? null) ? (string) $filters['category_id'] : null,
            'min_price' => filled($filters['min_price'] ?? null) ? (float) $filters['min_price'] : null,
            'max_price' => filled($filters['max_price'] ?? null) ? (float) $filters['max_price'] : null,
            'min_rating' => filled($filters['min_rating'] ?? null) ? (float) $filters['min_rating'] : null,
            'min_orders' => filled($filters['min_orders'] ?? null) ? (int) $filters['min_orders'] : null,
            'ship_to_country' => filled($filters['ship_to_country'] ?? null) ? Str::upper((string) $filters['ship_to_country']) : config('aliexpress.default_country'),
            'currency' => (string) config('aliexpress.default_currency', 'USD'),
            'language' => (string) config('aliexpress.default_language', 'EN'),
            'local' => (string) config('aliexpress.catalog.local', 'en_US'),
            'sort' => filled($filters['sort'] ?? null) ? (string) $filters['sort'] : null,
            'page' => max(1, (int) ($filters['page'] ?? 1)),
            'per_page' => min(50, max(1, (int) ($filters['per_page'] ?? 20))),
        ];
    }

    private function emptyResult(string $message, array $filters): array
    {
        return [
            'success' => true,
            'message' => $message,
            'items' => [],
            'meta' => $this->metaFromFilters($filters),
            'warnings' => [],
            'unsupported' => false,
        ];
    }

    private function resultWarnings(array $item, string $productId, mixed $image): array
    {
        $warnings = [];
        if ($productId === '') {
            $warnings[] = 'missing_product_id';
        }
        if (!$image) {
            $warnings[] = 'missing_image';
        }
        if (!is_numeric(Arr::get($item, 'target_sale_price') ?? Arr::get($item, 'targetSalePrice') ?? Arr::get($item, 'sale_price') ?? Arr::get($item, 'salePrice') ?? Arr::get($item, 'price'))) {
            $warnings[] = 'missing_price';
        }

        return $warnings;
    }

    private function fallbackCategories(): array
    {
        return collect((array) config('aliexpress.catalog.fallback_categories', []))
            ->map(fn (array $category) => [
                'id' => (string) ($category['id'] ?? ''),
                'parent_id' => null,
                'name' => (string) ($category['name'] ?? ''),
                'is_leaf' => false,
                'raw' => $category,
            ])
            ->filter(fn (array $category) => $category['id'] !== '' && $category['name'] !== '')
            ->values()
            ->all();
    }

    private function toFloat(mixed $value): ?float
    {
        if (is_string($value)) {
            $value = rtrim($value, '%');
        }

        return is_numeric($value) ? (float) $value : null;
    }

    private function toInt(mixed $value): ?int
    {
        if (is_string($value)) {
            $value = str_replace([',', '+'], '', $value);
        }

        return is_numeric($value) ? (int) $value : null;
    }

    private function normalizeRating(mixed $value): ?float
    {
        $rating = $this->toFloat($value);
        if ($rating === null) {
            return null;
        }

        return $rating > 5 ? round($rating / 20, 2) : $rating;
    }
}
