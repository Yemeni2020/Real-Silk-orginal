<?php

namespace App\Services\AliExpress;

use App\Models\AliExpressProduct;

class AliExpressProductImporter
{
    public function __construct(
        private readonly AliExpressClient $client,
        private readonly AliExpressTokenStore $tokenStore,
        private readonly AliExpressProductNormalizer $normalizer,
    ) {
    }

    public function import(
        string $productId,
        ?string $country = null,
        ?string $currency = null,
        ?string $language = null,
        ?float $margin = null,
    ): AliExpressProduct {
        $response = $this->client->getDropshippingProduct(
            $this->tokenStore->getValidAccessToken($this->client),
            $productId,
            $country ?: config('aliexpress.default_country'),
            $currency ?: config('aliexpress.default_currency'),
            $language ?: config('aliexpress.default_language'),
        );

        $data = $this->normalizer->normalize(
            $response,
            $margin ?? (float) config('aliexpress.default_margin')
        );

        return AliExpressProduct::query()->updateOrCreate(
            ['ali_express_product_id' => $data['ali_express_product_id']],
            $data
        );
    }
}
