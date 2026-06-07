<?php

namespace App\Services;

use App\Models\IntegrationLog;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Schema;
use Throwable;

class IntegrationLogService
{
    private const SENSITIVE_KEYS = [
        'access_token',
        'refresh_token',
        'app_secret',
        'secret',
        'session',
        'token',
        'authorization',
        'password',
        'payment',
        'shipping_address',
        'billing_address',
        'address',
        'phone',
        'email',
    ];

    public function log(
        string $provider,
        string $action,
        string $status,
        ?string $message = null,
        array $payload = [],
        ?string $externalId = null,
        ?int $createdBy = null,
        ?string $requestId = null,
    ): void {
        try {
            if (!Schema::hasTable('integration_logs')) {
                return;
            }

            IntegrationLog::query()->create([
                'provider' => $provider,
                'action' => $action,
                'external_id' => $externalId,
                'status' => $status,
                'message' => $message ? mb_substr($message, 0, 1000) : null,
                'payload' => $this->sanitize($payload),
                'request_id' => $requestId,
                'created_by' => $createdBy,
            ]);
        } catch (Throwable) {
            // Logging must never break business flows.
        }
    }

    public function sanitize(array $payload): array
    {
        return $this->sanitizeValue($payload);
    }

    private function sanitizeValue(mixed $value): mixed
    {
        if (!is_array($value)) {
            return $value;
        }

        $sanitized = [];
        foreach ($value as $key => $item) {
            $normalizedKey = strtolower((string) $key);
            if (Arr::first(self::SENSITIVE_KEYS, fn (string $sensitive) => str_contains($normalizedKey, $sensitive))) {
                $sanitized[$key] = '[redacted]';
                continue;
            }

            $sanitized[$key] = $this->sanitizeValue($item);
        }

        return $sanitized;
    }
}
