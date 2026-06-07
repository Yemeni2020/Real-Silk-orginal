<?php

namespace App\Services\AliExpress;

use App\Models\AliExpressProduct;
use App\Models\AliExpressProductPreview;
use App\Services\IntegrationLogService;
use Illuminate\Support\Str;
use RuntimeException;

class AliExpressProductPreviewService
{
    public function __construct(
        private readonly AliExpressClient $client,
        private readonly AliExpressTokenStore $tokenStore,
        private readonly AliExpressProductNormalizer $normalizer,
        private readonly AliExpressPricingService $pricingService,
        private readonly AliExpressProductPolicy $policy,
        private readonly AliExpressStoreProductPublisher $publisher,
        private readonly IntegrationLogService $integrationLog,
    ) {
    }

    public function extractProductId(string $input): string
    {
        $input = trim($input);

        if (preg_match('/\b(\d{10,20})\b/', $input, $matches) !== 1) {
            throw new RuntimeException('Could not detect AliExpress product ID. Paste a valid AliExpress item URL or a 10-20 digit product ID.');
        }

        return $matches[1];
    }

    public function createPreview(string $input, ?int $categoryId = null, ?int $adminId = null): AliExpressProductPreview
    {
        $productId = $this->extractProductId($input);

        $response = $this->client->getDropshippingProduct(
            $this->tokenStore->getValidAccessToken($this->client),
            $productId,
            config('aliexpress.default_country'),
            config('aliexpress.default_currency'),
            config('aliexpress.default_language'),
        );

        $normalized = $this->normalizer->normalize($response, (float) config('aliexpress.default_margin'));
        $pricing = $this->pricingService->calculate(
            (float) ($normalized['supplier_price'] ?? 0),
            $normalized['supplier_shipping_price'] ?? null,
        );
        $normalized['selling_price'] = $normalized['supplier_price'] !== null ? $pricing['selling_price'] : null;
        $policyResult = $this->policy->evaluate($normalized);
        $status = $policyResult['blocked'] ? 'blocked' : 'previewed';

        $preview = AliExpressProductPreview::query()->create([
            'source_input' => $input,
            'ali_express_product_id' => $productId,
            'title' => $normalized['title'] ?? null,
            'normalized_title' => $this->normalizeTitle((string) ($normalized['title'] ?? '')),
            'supplier_price' => $normalized['supplier_price'] ?? null,
            'supplier_shipping_price' => $normalized['supplier_shipping_price'] ?? null,
            'final_price' => $normalized['selling_price'] ?? null,
            'estimated_profit' => $pricing['profit'] ?? null,
            'currency' => $normalized['supplier_currency'] ?? $normalized['currency'] ?? config('aliexpress.default_currency'),
            'images' => $normalized['images'] ?? [],
            'variants' => $normalized['variants'] ?? [],
            'supplier_url' => $normalized['supplier_product_url'] ?? $normalized['supplier_url'] ?? null,
            'availability_status' => ($normalized['is_available'] ?? false) ? 'available' : 'unavailable',
            'warnings' => $policyResult['warnings'],
            'block_reasons' => $policyResult['block_reasons'],
            'policy_status' => $policyResult['blocked'] ? 'blocked' : 'allowed',
            'pricing_payload' => $pricing,
            'normalized_payload' => $normalized,
            'raw_payload' => $response,
            'status' => $status,
            'category_id' => $categoryId,
            'created_by' => $adminId,
            'message' => $policyResult['blocked'] ? 'Preview blocked by product policy.' : 'Preview ready.',
        ]);

        $this->integrationLog->log('aliexpress', 'product_preview_created', $status, $preview->message, [
            'preview_id' => $preview->id,
            'policy_status' => $preview->policy_status,
            'warnings' => $preview->warnings,
            'block_reasons' => $preview->block_reasons,
        ], $productId, $adminId);

        return $preview;
    }

    public function importFromPreview(AliExpressProductPreview $preview, ?int $adminId = null): AliExpressProduct
    {
        $data = $this->productDataFromPreview($preview);
        $product = AliExpressProduct::query()->updateOrCreate(
            ['ali_express_product_id' => $preview->ali_express_product_id],
            $data
        );

        $preview->update([
            'status' => $preview->policy_status === 'blocked' ? 'blocked' : 'imported',
            'imported_at' => now(),
            'message' => 'Imported supplier data from preview.',
        ]);

        $this->integrationLog->log('aliexpress', 'product_imported', 'success', 'Imported from preview.', [
            'preview_id' => $preview->id,
        ], $preview->ali_express_product_id, $adminId);

        return $product;
    }

    public function publishFromPreview(AliExpressProductPreview $preview, bool $updateExisting = false, ?int $adminId = null): array
    {
        if ($preview->policy_status === 'blocked' || !empty($preview->block_reasons)) {
            $preview->update([
                'status' => 'blocked',
                'message' => 'Publishing blocked by product policy: ' . implode(', ', (array) $preview->block_reasons),
            ]);
            throw new AliExpressProductBlockedException((array) $preview->block_reasons);
        }

        $product = $this->importFromPreview($preview, $adminId);
        $result = $this->publisher->publishWithStatus($product, $preview->category_id);
        $preview->update([
            'status' => $updateExisting ? 'updated' : 'published',
            'local_product_id' => $result['product']->id,
            'published_at' => now(),
            'message' => $updateExisting ? 'Existing product updated from preview.' : 'Product published from preview.',
        ]);

        return $result;
    }

    public function skipPreview(AliExpressProductPreview $preview, ?string $message = null, ?int $adminId = null): AliExpressProductPreview
    {
        $preview->update([
            'status' => 'skipped',
            'skipped_at' => now(),
            'message' => $message ?: 'Skipped by admin.',
        ]);

        $this->integrationLog->log('aliexpress', 'product_preview_skipped', 'skipped', $preview->message, [
            'preview_id' => $preview->id,
        ], $preview->ali_express_product_id, $adminId);

        return $preview;
    }

    private function productDataFromPreview(AliExpressProductPreview $preview): array
    {
        $normalized = (array) $preview->normalized_payload;

        return array_merge($normalized, [
            'ali_express_product_id' => $preview->ali_express_product_id,
            'title' => $preview->title,
            'supplier_price' => $preview->supplier_price,
            'supplier_shipping_price' => $preview->supplier_shipping_price,
            'selling_price' => $preview->final_price,
            'currency' => $preview->currency,
            'supplier_currency' => $preview->currency,
            'images' => $preview->images ?? [],
            'variants' => $preview->variants ?? [],
            'supplier_url' => $preview->supplier_url,
            'supplier_product_url' => $preview->supplier_url,
            'is_available' => $preview->availability_status === 'available',
            'is_active' => $preview->availability_status === 'available',
            'sync_status' => $preview->policy_status === 'blocked' ? 'blocked' : 'synced',
            'block_reason' => $preview->policy_status === 'blocked' ? implode(', ', (array) $preview->block_reasons) : null,
            'warning_flags' => $preview->warnings ?? [],
            'raw_payload' => $preview->raw_payload ?? [],
            'last_synced_at' => now(),
        ]);
    }

    private function normalizeTitle(string $title): string
    {
        $title = trim(preg_replace('/\s+/', ' ', strip_tags($title)) ?: '');

        return Str::limit($title, 190, '');
    }
}
