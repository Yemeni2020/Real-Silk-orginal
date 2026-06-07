<?php

namespace App\Services\AliExpress;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Arr;
use RuntimeException;

class AliExpressClient
{
    private const SIGN_METHOD = 'sha256';

    public function __construct(
        private readonly ?string $appKey = null,
        private readonly ?string $appSecret = null,
        private readonly ?string $syncBaseUrl = null,
        private readonly ?string $restBaseUrl = null,
        private readonly ?string $authorizeUrl = null,
        private readonly ?string $redirectUri = null,
        private readonly ?Client $http = null,
    ) {
    }

    public function getAuthorizationUrl(?string $state = null): string
    {
        $query = array_filter([
            'response_type' => 'code',
            'force_auth' => 'true',
            'redirect_uri' => $this->getRedirectUri(),
            'client_id' => $this->getAppKey(),
            'state' => $state,
        ], static fn ($value) => $value !== null && $value !== '');

        return $this->getAuthorizeUrl() . '?' . http_build_query($query);
    }

    public function exchangeCodeForToken(string $code, ?string $uuid = null): array
    {
        return $this->call('/auth/token/security/create', array_filter([
            'code' => $code,
            'uuid' => $uuid,
        ], static fn ($value) => $value !== null && $value !== ''));
    }

    public function refreshSecurityToken(string $refreshToken): array
    {
        return $this->call('/auth/token/security/refresh', [
            'refresh_token' => $refreshToken,
        ]);
    }

    public function getDropshippingProduct(
        string $accessToken,
        string $productId,
        ?string $country = null,
        ?string $currency = null,
        ?string $language = null,
    ): array {
        return $this->call('aliexpress.ds.product.get', array_filter([
            'product_id' => $productId,
            'ship_to_country' => $country,
            'target_currency' => $currency,
            'target_language' => $language,
        ], static fn ($value) => $value !== null && $value !== ''), $accessToken);
    }

    public function getProductDetail(string $productId, ?string $accessToken = null): array
    {
        return $this->getDropshippingProduct(
            (string) $accessToken,
            $productId,
            config('aliexpress.default_country'),
            config('aliexpress.default_currency'),
            config('aliexpress.default_language'),
        );
    }

    public function searchProducts(array $filters): array
    {
        $accessToken = (string) ($filters['access_token'] ?? '');
        unset($filters['access_token']);

        try {
            $response = $this->call((string) config('aliexpress.catalog.search_method', 'aliexpress.ds.text.search'), array_filter([
                'key_word' => $filters['keyword'] ?? null,
                'keyWord' => $filters['keyword'] ?? null,
                'category_id' => $filters['category_id'] ?? null,
                'categoryId' => $filters['category_id'] ?? null,
                'min_price' => $filters['min_price'] ?? null,
                'max_price' => $filters['max_price'] ?? null,
                'ship_to_country' => $filters['ship_to_country'] ?? config('aliexpress.default_country'),
                'countryCode' => $filters['ship_to_country'] ?? config('aliexpress.default_country'),
                'target_currency' => $filters['currency'] ?? config('aliexpress.default_currency'),
                'currency' => $filters['currency'] ?? config('aliexpress.default_currency'),
                'target_language' => $filters['language'] ?? config('aliexpress.default_language'),
                'locale' => $filters['language'] ?? config('aliexpress.default_language'),
                'local' => $filters['local'] ?? config('aliexpress.catalog.local', 'en_US'),
                'sort' => $filters['sort'] ?? null,
                'page_no' => $filters['page'] ?? 1,
                'page_size' => $filters['per_page'] ?? 20,
                'pageNo' => $filters['page'] ?? 1,
                'pageSize' => $filters['per_page'] ?? 20,
            ], static fn ($value) => $value !== null && $value !== ''), $accessToken ?: null);

            return [
                'success' => true,
                'message' => null,
                'data' => $response,
                'meta' => [],
                'warnings' => [],
            ];
        } catch (RuntimeException $exception) {
            return [
                'success' => false,
                'message' => $this->friendlyCatalogError($exception),
                'data' => [],
                'meta' => [],
                'warnings' => [$exception->getMessage()],
            ];
        }
    }

    public function getCategories(?string $parentId = null, ?string $accessToken = null): array
    {
        try {
            $response = $this->call((string) config('aliexpress.catalog.category_method', 'aliexpress.ds.category.get'), array_filter([
                'parent_category_id' => $parentId,
                'language' => config('aliexpress.default_language'),
            ], static fn ($value) => $value !== null && $value !== ''), $accessToken ?: null);

            return [
                'success' => true,
                'message' => null,
                'data' => $response,
                'warnings' => [],
            ];
        } catch (RuntimeException $exception) {
            return [
                'success' => false,
                'message' => 'AliExpress category browsing is not available for this API account.',
                'data' => [],
                'warnings' => [$exception->getMessage()],
            ];
        }
    }

    public function call(string $method, array $params = [], ?string $session = null): array
    {
        $parameters = array_merge($params, [
            'method' => $method,
            'app_key' => $this->getAppKey(),
            'sign_method' => self::SIGN_METHOD,
            'timestamp' => (int) round(microtime(true) * 1000),
        ]);

        if ($session !== null && $session !== '') {
            $parameters['session'] = $session;
            $parameters['simplify'] = 'true';
        }

        $parameters['sign'] = $this->sign($parameters);

        try {
            $response = $this->getHttp()->request('POST', $this->assembleUrl($parameters), [
                'http_errors' => false,
                'headers' => [
                    'Accept' => 'application/json',
                ],
            ]);
        } catch (GuzzleException $exception) {
            throw new RuntimeException('AliExpress request failed: ' . $exception->getMessage(), 0, $exception);
        }

        $decoded = json_decode((string) $response->getBody(), true);

        if (!is_array($decoded)) {
            throw new RuntimeException('AliExpress returned a non-JSON response.');
        }

        if (isset($decoded['error_response'])) {
            $error = $decoded['error_response'];
            $message = Arr::get($error, 'sub_msg')
                ?? Arr::get($error, 'msg')
                ?? 'Unknown AliExpress API error.';

            throw new RuntimeException($message);
        }

        return $decoded;
    }

    private function sign(array $parameters): string
    {
        $params = $parameters;
        $baseString = '';

        if (str_contains((string) $params['method'], '/')) {
            $baseString = (string) $params['method'];
            unset($params['method']);
        }

        ksort($params, SORT_STRING);

        foreach ($params as $key => $value) {
            if ($value === null) {
                continue;
            }

            $baseString .= $key . (is_bool($value) ? ($value ? 'true' : 'false') : (string) $value);
        }

        return strtoupper(hash_hmac('sha256', $baseString, $this->getAppSecret()));
    }

    private function assembleUrl(array $parameters): string
    {
        $params = $parameters;
        $method = (string) $params['method'];
        $baseUrl = str_contains($method, '/')
            ? rtrim($this->getRestBaseUrl(), '/') . $method
            : rtrim($this->getSyncBaseUrl(), '/');

        if (str_contains($method, '/')) {
            unset($params['method']);
        }

        ksort($params, SORT_STRING);

        return $baseUrl . '?' . http_build_query($params);
    }

    private function getHttp(): Client
    {
        return $this->http ?? new Client(['timeout' => 30]);
    }

    private function getAppKey(): string
    {
        return $this->appKey ?? (string) config('aliexpress.app_key');
    }

    private function getAppSecret(): string
    {
        return $this->appSecret ?? (string) config('aliexpress.app_secret');
    }

    private function getSyncBaseUrl(): string
    {
        return $this->syncBaseUrl ?? (string) config('aliexpress.sync_base_url');
    }

    private function getRestBaseUrl(): string
    {
        return $this->restBaseUrl ?? (string) config('aliexpress.rest_base_url');
    }

    private function getAuthorizeUrl(): string
    {
        return $this->authorizeUrl ?? (string) config('aliexpress.authorize_url');
    }

    private function getRedirectUri(): string
    {
        return $this->redirectUri ?? (string) config('aliexpress.redirect_uri');
    }

    private function friendlyCatalogError(RuntimeException $exception): string
    {
        $message = strtolower($exception->getMessage());
        if (str_contains($message, 'permission') || str_contains($message, 'isv') || str_contains($message, 'method') || str_contains($message, 'invalid api')) {
            return 'AliExpress catalog search is not available for this API account. You can still import by product URL or ID.';
        }

        return 'AliExpress catalog search failed. Please try again later or import by product URL or ID.';
    }
}
