<?php

namespace App\Services\AliExpress;

use App\Models\BusinessSetting;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Crypt;
use RuntimeException;

class AliExpressTokenStore
{
    private const TOKEN_SETTING = 'aliexpress_tokens';
    private const STATUS_CONNECTED = 'connected';
    private const STATUS_DISCONNECTED = 'disconnected';
    private const STATUS_TOKEN_EXPIRED = 'token_expired';
    private const STATUS_RECONNECT_REQUIRED = 'reconnect_required';

    public function getTokens(): ?array
    {
        $value = BusinessSetting::where('type', self::TOKEN_SETTING)->value('value');

        if (!$value) {
            return null;
        }

        $decoded = json_decode($value, true);
        if (!is_array($decoded)) {
            return null;
        }

        return $this->decryptTokenFields($decoded);
    }

    public function storeTokens(array $tokens): array
    {
        $existing = $this->getTokens() ?? [];
        $payload = array_merge($existing, $tokens, $this->expiryMetadata($tokens), [
            'connection_status' => self::STATUS_CONNECTED,
            'connected_at' => now()->toIso8601String(),
            'last_auth_error' => null,
            'stored_at' => now()->toIso8601String(),
        ]);

        BusinessSetting::query()->updateOrCreate(
            ['type' => self::TOKEN_SETTING],
            ['value' => json_encode($this->encryptTokenFields($payload), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)]
        );

        return $payload;
    }

    public function markAuthError(string $message): void
    {
        $payload = array_merge($this->getTokens() ?? [], [
            'connection_status' => self::STATUS_RECONNECT_REQUIRED,
            'last_auth_error' => mb_substr($message, 0, 1000),
            'last_auth_error_at' => now()->toIso8601String(),
        ]);

        BusinessSetting::query()->updateOrCreate(
            ['type' => self::TOKEN_SETTING],
            ['value' => json_encode($this->encryptTokenFields($payload), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)]
        );
    }

    public function getConnectionStatus(?AliExpressClient $client = null): array
    {
        $tokens = $this->getTokens();
        if (!$tokens || empty($tokens['access_token'])) {
            return [
                'status' => self::STATUS_DISCONNECTED,
                'message' => 'AliExpress is disconnected.',
                'connected' => false,
                'last_auth_error' => $tokens['last_auth_error'] ?? null,
            ];
        }

        if ($this->isRefreshExpired($tokens)) {
            return [
                'status' => self::STATUS_RECONNECT_REQUIRED,
                'message' => 'Reconnect required. Refresh token expired or is missing.',
                'connected' => false,
                'connected_at' => $tokens['connected_at'] ?? null,
                'last_auth_error' => $tokens['last_auth_error'] ?? null,
            ];
        }

        if ($this->isExpired($tokens)) {
            return [
                'status' => self::STATUS_TOKEN_EXPIRED,
                'message' => 'Access token expired. It will be refreshed automatically if possible.',
                'connected' => true,
                'connected_at' => $tokens['connected_at'] ?? null,
                'token_expires_at' => $tokens['token_expires_at'] ?? null,
                'last_auth_error' => $tokens['last_auth_error'] ?? null,
            ];
        }

        return [
            'status' => $tokens['connection_status'] ?? self::STATUS_CONNECTED,
            'message' => 'AliExpress is connected.',
            'connected' => true,
            'connected_at' => $tokens['connected_at'] ?? null,
            'token_expires_at' => $tokens['token_expires_at'] ?? null,
            'refresh_token_expires_at' => $tokens['refresh_token_expires_at'] ?? null,
            'last_auth_error' => $tokens['last_auth_error'] ?? null,
        ];
    }

    public function getValidAccessToken(AliExpressClient $client): string
    {
        $tokens = $this->getTokens();

        if (!$tokens || empty($tokens['access_token'])) {
            throw new RuntimeException('AliExpress is not authorized yet. Open /integrations/aliexpress/connect first.');
        }

        if ($this->isRefreshExpired($tokens)) {
            throw new RuntimeException('AliExpress reconnect required. Refresh token is expired or missing.');
        }

        if ($this->isExpired($tokens) && !empty($tokens['refresh_token'])) {
            try {
                $tokens = $this->storeTokens($client->refreshSecurityToken($tokens['refresh_token']));
            } catch (RuntimeException $exception) {
                $this->markAuthError($exception->getMessage());
                throw new RuntimeException('AliExpress token refresh failed. Reconnect AliExpress.', 0, $exception);
            }
        }

        return (string) $tokens['access_token'];
    }

    public function isExpired(?array $tokens = null): bool
    {
        $tokens = $tokens ?? $this->getTokens() ?? [];
        $expiresAt = $tokens['token_expires_at'] ?? null;
        $expireTime = $tokens['expire_time'] ?? null;

        if ($expiresAt) {
            return now()->addMinutes(5)->greaterThanOrEqualTo(Carbon::parse($expiresAt));
        }

        if (!$expireTime) {
            return false;
        }

        return now()->addMinutes(5)->greaterThanOrEqualTo(Carbon::createFromTimestampMs((int) $expireTime));
    }

    private function isRefreshExpired(array $tokens): bool
    {
        $expiresAt = $tokens['refresh_token_expires_at'] ?? null;
        if (!$expiresAt) {
            return empty($tokens['refresh_token']);
        }

        return now()->greaterThanOrEqualTo(Carbon::parse($expiresAt));
    }

    private function expiryMetadata(array $tokens): array
    {
        $metadata = [];
        if (!empty($tokens['expire_time'])) {
            $metadata['token_expires_at'] = Carbon::createFromTimestampMs((int) $tokens['expire_time'])->toIso8601String();
        } elseif (!empty($tokens['expires_in'])) {
            $metadata['token_expires_at'] = now()->addSeconds((int) $tokens['expires_in'])->toIso8601String();
        }

        if (!empty($tokens['refresh_token_valid_time'])) {
            $metadata['refresh_token_expires_at'] = Carbon::createFromTimestampMs((int) $tokens['refresh_token_valid_time'])->toIso8601String();
        } elseif (!empty($tokens['refresh_expires_in'])) {
            $metadata['refresh_token_expires_at'] = now()->addSeconds((int) $tokens['refresh_expires_in'])->toIso8601String();
        }

        return $metadata;
    }

    private function encryptTokenFields(array $payload): array
    {
        foreach (['access_token', 'refresh_token'] as $field) {
            if (!empty($payload[$field]) && !$this->isEncrypted((string) $payload[$field])) {
                $payload[$field] = Crypt::encryptString((string) $payload[$field]);
            }
        }

        return $payload;
    }

    private function decryptTokenFields(array $payload): array
    {
        foreach (['access_token', 'refresh_token'] as $field) {
            if (empty($payload[$field])) {
                continue;
            }

            try {
                $payload[$field] = Crypt::decryptString((string) $payload[$field]);
            } catch (\Throwable) {
                // Plaintext legacy tokens are returned and re-encrypted on next storeTokens call.
            }
        }

        return $payload;
    }

    private function isEncrypted(string $value): bool
    {
        try {
            Crypt::decryptString($value);
            return true;
        } catch (\Throwable) {
            return false;
        }
    }
}
