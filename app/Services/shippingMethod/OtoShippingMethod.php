<?php
namespace App\Services\shippingMethod;

use App\Services\shippingMethod\Contracts\ShippingMethodInterface;
use App\Traits\FileManagerTrait;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

class OtoShippingMethod implements ShippingMethodInterface{

    private $api;
    function __construct(){
        $this->api='AMf-vBzt7maqh6p6RfZxbpDD_ATuZ0Qy_lpd49iekHuHfJK7olCyNiCCROuulT2fYw6LWEFOKtAXbXfx88VDIXt6knOFJff9DUJv6uOr68npCUhBn1_-0W0zSOE5t7sleQZ0JzMOeBvC0MeOCyM2v_pjszE0R07cpaQIfB25WpJaKUj4-I22vMiMDjoQOwbcG7BNUDcJ3D4g9AS1F3Orh4qR_ricllHQ8g';
    }

    protected $refreshToken = "AMf-vBzt7maqh6p6RfZxbpDD_ATuZ0Qy_lpd49iekHuHfJK7olCyNiCCROuulT2fYw6LWEFOKtAXbXfx88VDIXt6knOFJff9DUJv6uOr68npCUhBn1_-0W0zSOE5t7sleQZ0JzMOeBvC0MeOCyM2v_pjszE0R07cpaQIfB25WpJaKUj4-I22vMiMDjoQOwbcG7BNUDcJ3D4g9AS1F3Orh4qR_ricllHQ8g";

    public function getAccessToken()
    {
        // جلب التوكن من الكاش إن وجد
        if (Cache::has('oto_access_token')) {
            return Cache::get('oto_access_token');
        }

        // طلب جديد من OTO
        $response = Http::post('https://api.tryoto.com/rest/v2/refreshToken', [
            'refresh_token' => $this->refreshToken,
        ]);

        if ($response->successful()) {
            $accessToken = $response['access_token'];

            // خزنه لمدة 55 دقيقة
            Cache::put('oto_access_token', $accessToken, now()->addMinutes(55));

            return $accessToken;
        }

        return null;
    }

    public function getShippingRates($fromCity, $toCity, $weight)
    {
        $accessToken = $this->getAccessToken();

        if (!$accessToken) {
            return ['error' => 'فشل في الحصول على التوكن'];
        }

        $headers = [
            'Authorization' => 'Bearer ' . $accessToken,
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
        ];

        $body = [
            "weight"=>"3",
            "originCity"=>"Riyadh",
            "destinationCity"=>"Jeddah",
            "height"=>30,
            "width"=>30,
            "length"=>30
        ];

        $response = Http::withHeaders($headers)
            ->post('https://api.tryoto.com/rest/v2/checkOTODeliveryFee', $body);

        return $response->json();
    }
    public function getApi($cart){
        

        $cart=\App\Models\Cart::whereHas('product', function ($query) {
            return $query->active();
        })->where(['customer_id' => (auth('customer')->check() ? auth('customer')->id() : session('guest_id'))])
        ->get()->groupBy('cart_group_id');
        foreach ($cart as  $key => $group) {
            foreach ($group as $key => $value) {
                # code...
                $body=[
                    "weight"=>"3",
                    "originCity"=>"Riyadh",
                    "destinationCity"=>"Jeddah",
                    "height"=>30,
                    "width"=>30,
                    "length"=>30
                ];
                return response()->json($value);
            }
        }
        
        $oto = new \App\Services\ShippingMethod\OtoShippingMethod();

        $rates = $oto->getShippingRates([
            'country' => 'SA',
            'city' => 'Riyadh',
            'postal_code' => '12271',
        ], [
            'country' => 'SA',
            'city' => 'Jeddah',
            'postal_code' => '21577',
        ], 2.5); 

        return response()->json($rates);

        return response()->json(['message' => 'This method does not support rates yet']);
    }
}