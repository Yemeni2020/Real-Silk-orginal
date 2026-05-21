<?php

namespace App\Http\Controllers\Integrations;

use App\Http\Controllers\Controller;
use App\Services\AliExpress\AliExpressClient;
use App\Services\AliExpress\AliExpressTokenStore;
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

    public function callback(Request $request, AliExpressClient $client, AliExpressTokenStore $tokenStore): JsonResponse
    {
        $expectedState = $request->session()->pull('aliexpress_oauth_state');
        $incomingState = $request->string('state')->toString();
        $code = $request->string('code')->toString();

        if ($expectedState && $incomingState !== $expectedState) {
            return response()->json(['message' => 'AliExpress state verification failed.'], 422);
        }

        if ($code === '') {
            return response()->json([
                'message' => 'AliExpress did not return an authorization code.',
                'query' => $request->query(),
            ], 422);
        }

        try {
            $tokens = $client->exchangeCodeForToken($code, $request->string('uuid')->toString() ?: null);
            $stored = $tokenStore->storeTokens($tokens);
        } catch (RuntimeException $exception) {
            return response()->json(['message' => $exception->getMessage()], 422);
        }

        return response()->json([
            'message' => 'AliExpress connected successfully.',
            'account_id' => $stored['account_id'] ?? null,
            'seller_id' => $stored['seller_id'] ?? null,
            'expire_time' => $stored['expire_time'] ?? null,
        ]);
    }
}
