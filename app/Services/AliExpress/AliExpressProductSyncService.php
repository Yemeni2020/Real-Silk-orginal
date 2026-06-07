<?php

namespace App\Services\AliExpress;

use App\Models\AliExpressProduct;
use App\Models\Product;
use App\Services\IntegrationLogService;
use Illuminate\Support\Arr;

class AliExpressProductSyncService
{
    public function __construct(
        private readonly AliExpressClient $client,
        private readonly AliExpressTokenStore $tokenStore,
        private readonly AliExpressProductNormalizer $normalizer,
        private readonly AliExpressProductPolicy $policy,
        private readonly AliExpressPricingService $pricingService,
        private readonly IntegrationLogService $integrationLog,
    ) {
    }

    public function sync(AliExpressProduct $source, bool $dryRun = false): array
    {
        $externalId = (string) $source->ali_express_product_id;
        $this->integrationLog->log('aliexpress', 'product_sync_started', 'processing', null, [], $externalId);

        try {
            $response = $this->client->getDropshippingProduct(
                $this->tokenStore->getValidAccessToken($this->client),
                $externalId,
                config('aliexpress.default_country'),
                config('aliexpress.default_currency'),
                config('aliexpress.default_language'),
            );

            $data = $this->normalizer->normalize($response, (float) ($source->margin ?? config('aliexpress.default_margin')));
            $policyResult = $this->policy->evaluate($data);
            if ($policyResult['blocked']) {
                $data['sync_status'] = 'blocked';
                $data['block_reason'] = implode(', ', $policyResult['block_reasons']);
            }
            $data['warning_flags'] = $policyResult['warnings'];

            if ($dryRun) {
                return [
                    'status' => $data['sync_status'] ?? 'synced',
                    'dry_run' => true,
                    'changes' => $this->diff($source, $data),
                    'warnings' => $policyResult['warnings'],
                    'block_reasons' => $policyResult['block_reasons'],
                ];
            }

            $source->update($data);
            $localUpdate = $this->updateLocalProductSafely($source->fresh());
            $this->integrationLog->log('aliexpress', 'product_sync_finished', $source->sync_status ?: 'synced', null, [
                'local_update' => $localUpdate,
                'warnings' => $policyResult['warnings'],
            ], $externalId);

            return [
                'status' => $source->sync_status ?: 'synced',
                'dry_run' => false,
                'local_update' => $localUpdate,
                'warnings' => $policyResult['warnings'],
                'block_reasons' => $policyResult['block_reasons'],
            ];
        } catch (\Throwable $exception) {
            if (!$dryRun) {
                $source->update([
                    'sync_status' => 'failed',
                    'sync_error' => $exception->getMessage(),
                    'last_synced_at' => now(),
                ]);
            }
            $this->integrationLog->log('aliexpress', 'product_sync_failed', 'failed', $exception->getMessage(), [], $externalId);

            throw $exception;
        }
    }

    private function updateLocalProductSafely(AliExpressProduct $source): array
    {
        $product = $source->local_product_id
            ? Product::query()->find($source->local_product_id)
            : Product::query()->where('code', 'AE-' . $source->ali_express_product_id)->first();

        if (!$product) {
            return ['status' => 'skipped', 'reason' => 'local_product_missing'];
        }

        $updates = [];
        if ((bool) config('aliexpress.sync.safe_update_price', true) && is_numeric($source->supplier_price)) {
            $updates['unit_price'] = $this->pricingService->calculate((float) $source->supplier_price, $source->supplier_shipping_price)['selling_price'];
            $updates['purchase_price'] = (float) $source->supplier_price;
        }
        if ((bool) config('aliexpress.sync.safe_update_stock', true) && is_numeric($source->stock)) {
            $updates['current_stock'] = max(0, (int) $source->stock);
        }
        if (!$source->is_available || $source->sync_status === 'unavailable') {
            $updates['status'] = 0;
            $updates['published'] = 0;
        }

        if (empty($updates)) {
            return ['status' => 'skipped', 'reason' => 'no_safe_updates'];
        }

        $product->update($updates);

        return ['status' => 'updated', 'fields' => array_keys($updates)];
    }

    private function diff(AliExpressProduct $source, array $data): array
    {
        $changes = [];
        foreach (['supplier_price', 'selling_price', 'stock', 'supplier_stock_status', 'sync_status', 'is_available'] as $key) {
            $current = Arr::get($source->toArray(), $key);
            $next = Arr::get($data, $key);
            if ($current != $next) {
                $changes[$key] = ['from' => $current, 'to' => $next];
            }
        }

        return $changes;
    }
}
