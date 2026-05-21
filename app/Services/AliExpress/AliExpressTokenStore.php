<?php

namespace App\Services\AliExpress;

use App\Models\BusinessSetting;
use Illuminate\Support\Carbon;
use RuntimeException;

class AliExpressTokenStore
{
    private const TOKEN_SETTING = 'aliexpress_tokens';

    public function getTokens(): ?array
    {
        $value = BusinessSetting::where('type', self::TOKEN_SETTING)->value('value');

        if (!$value) {
            return null;
        }

        $decoded = json_decode($value, true);

        return is_array($decoded) ? $decoded : null;
    }

    public function storeTokens(array $tokens): array
    {
        $payload = array_merge($this->getTokens() ?? [], $tokens, [
            'stored_at' => now()->toIso8601String(),
        ]);

        BusinessSetting::query()->updateOrCreate(
            ['type' => self::TOKEN_SETTING],
            ['value' => json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)]
        );

        return $payload;
    }

    public function getValidAccessToken(AliExpressClient $client): string
    {
        $tokens = $this->getTokens();

        if (!$tokens || empty($tokens['access_token'])) {
            throw new RuntimeException('AliExpress is not authorized yet. Open /integrations/aliexpress/connect first.');
        }

        if ($this->isExpired($tokens) && !empty($tokens['refresh_token'])) {
            $tokens = $this->storeTokens($client->refreshSecurityToken($tokens['refresh_token']));
        }

        return (string) $tokens['access_token'];
    }

    private function isExpired(array $tokens): bool
    {
        $expireTime = $tokens['expire_time'] ?? null;

        if (!$expireTime) {
            return false;
        }

        return now()->addMinutes(5)->greaterThanOrEqualTo(
            Carbon::createFromTimestampMs((int) $expireTime)
        );
    }
}
