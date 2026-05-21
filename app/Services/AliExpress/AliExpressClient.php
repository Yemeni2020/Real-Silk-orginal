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
}
