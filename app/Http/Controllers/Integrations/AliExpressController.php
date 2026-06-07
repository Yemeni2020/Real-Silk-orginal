<?php

namespace App\Http\Controllers\Integrations;

use App\Http\Controllers\Controller;
use App\Services\AliExpress\AliExpressClient;
use App\Services\AliExpress\AliExpressTokenStore;
use App\Services\IntegrationLogService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use RuntimeException;

class AliExpressController extends Controller
{
    public function connect(Request $request, AliExpressClient $client): RedirectResponse
    {
        $state = Str::random(40);
        $request->session()->put('aliexpress_oauth_state', $state);

        return redirect()->away($client->getAuthorizationUrl($state));
    }

    public function callback(Request $request, AliExpressClient $client, AliExpressTokenStore $tokenStore, IntegrationLogService $integrationLog): JsonResponse
    {
        $expectedState = $request->session()->pull('aliexpress_oauth_state');
        $incomingState = $request->string('state')->toString();
        $code = $request->string('code')->toString();

        if (!$expectedState || $incomingState === '' || $incomingState !== $expectedState) {
            $integrationLog->log('aliexpress', 'oauth_failed', 'failed', 'AliExpress state verification failed.');
            return response()->json(['message' => 'AliExpress state verification failed.'], 422);
        }

        if ($code === '') {
            $integrationLog->log('aliexpress', 'oauth_failed', 'failed', 'AliExpress did not return an authorization code.');
            return response()->json([
                'message' => 'AliExpress did not return an authorization code.',
                'query' => $request->query(),
            ], 422);
        }

        try {
            $tokens = $client->exchangeCodeForToken($code, $request->string('uuid')->toString() ?: null);
            $stored = $tokenStore->storeTokens($tokens);
        } catch (RuntimeException $exception) {
            $tokenStore->markAuthError($exception->getMessage());
            $integrationLog->log('aliexpress', 'oauth_failed', 'failed', $exception->getMessage());
            return response()->json(['message' => $exception->getMessage()], 422);
        }

        $integrationLog->log('aliexpress', 'oauth_connected', 'success', null, [
            'account_id' => $stored['account_id'] ?? null,
            'seller_id' => $stored['seller_id'] ?? null,
            'expire_time' => $stored['expire_time'] ?? null,
        ]);

        return response()->json([
            'message' => 'AliExpress connected successfully.',
            'account_id' => $stored['account_id'] ?? null,
            'seller_id' => $stored['seller_id'] ?? null,
            'expire_time' => $stored['expire_time'] ?? null,
        ]);
    }
}
