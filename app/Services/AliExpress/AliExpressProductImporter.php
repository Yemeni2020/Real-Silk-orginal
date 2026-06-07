<?php

namespace App\Services\AliExpress;

use App\Models\AliExpressProduct;
use App\Services\IntegrationLogService;

class AliExpressProductImporter
{
    public function __construct(
        private readonly AliExpressClient $client,
        private readonly AliExpressTokenStore $tokenStore,
        private readonly AliExpressProductNormalizer $normalizer,
        private readonly AliExpressProductPolicy $policy,
        private readonly IntegrationLogService $integrationLog,
    ) {
    }

    public function import(
        string $productId,
        ?string $country = null,
        ?string $currency = null,
        ?string $language = null,
        ?float $margin = null,
    ): AliExpressProduct {
        try {
            $response = $this->client->getDropshippingProduct(
                $this->tokenStore->getValidAccessToken($this->client),
                $productId,
                $country ?: config('aliexpress.default_country'),
                $currency ?: config('aliexpress.default_currency'),
                $language ?: config('aliexpress.default_language'),
            );
        } catch (\Throwable $exception) {
            AliExpressProduct::query()->where('ali_express_product_id', $productId)->update([
                'sync_status' => 'failed',
                'sync_error' => $exception->getMessage(),
                'last_synced_at' => now(),
            ]);
            $this->integrationLog->log('aliexpress', 'product_import_failed', 'failed', $exception->getMessage(), [
                'product_id' => $productId,
            ], $productId);
            throw $exception;
        }

        $data = $this->normalizer->normalize(
            $response,
            $margin ?? (float) config('aliexpress.default_margin')
        );

        $policyResult = $this->policy->evaluate($data);
        $data['block_reason'] = $policyResult['blocked'] ? implode(', ', $policyResult['block_reasons']) : null;
        $data['warning_flags'] = $policyResult['warnings'];
        if ($policyResult['blocked']) {
            $data['sync_status'] = 'blocked';
        }

        $product = AliExpressProduct::query()->updateOrCreate(
            ['ali_express_product_id' => $data['ali_express_product_id']],
            $data
        );

        $this->integrationLog->log('aliexpress', 'product_imported', $policyResult['blocked'] ? 'blocked' : 'success', null, [
            'product_id' => $productId,
            'sync_status' => $product->sync_status,
            'warnings' => $policyResult['warnings'],
            'block_reasons' => $policyResult['block_reasons'],
        ], $productId);

        return $product;
    }
}
