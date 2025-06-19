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
            "weight"=>$weight,
            "originCity"=>$fromCity,
            "destinationCity"=>$toCity,
            "currency"=>"KWD",
        ];

        $response = Http::withHeaders($headers)
            ->post('https://api.tryoto.com/rest/v2/checkOTODeliveryFee', $body);

        return $response->json();
    }
    public function getApi($shop_id)
    {
        $shippingAddress = \App\Models\ShippingAddress::find(session()->get('address_id'));
    
        if (!$shippingAddress) {
            return response()->json(['error' => 'لم يتم العثور على عنوان الشحن'], 404);
        }
    
        $userId = auth('customer')->check() ? auth('customer')->id() : session('guest_id');
    
        $type_shop = $shop_id == 0 ? "admin" : "seller";
    
        $cartItems = \App\Models\Cart::whereHas('product', fn($q) => $q->active())
            ->where('customer_id', $userId)
            ->where("seller_is", $type_shop)
            ->with(["seller.shop", "product"]);
    
        if ($type_shop === "seller") {
            $cartItems = $cartItems->where("seller_id", $shop_id);
        }
    
        $cartItems = $cartItems->get();
    
        if ($cartItems->isEmpty()) {
            return response()->json(['error' => 'لا يوجد منتجات في السلة'], 404);
        }
    
        $oto = new \App\Services\ShippingMethod\OtoShippingMethod();
    
        // ✅ تجميع الوزن
        $totalWeight = $cartItems->sum(fn($item) => $item->product->weight ?? 1);
    
        // ✅ تحديد المدينة الأصل
        $firstItem = $cartItems->first();
        $originCity = $shop_id == 0
            ? 'Dammam'
            : ($firstItem->seller->shop->city ?? 'Riyadh');
    
        $destinationCity = $shippingAddress->city ?? 'Jeddah';
    
        // ✅ استدعاء OTO لحساب سعر الشحن الإجمالي
        $rate = $oto->getShippingRates($originCity, $destinationCity, $totalWeight);
    
        // ✅ عرض النتائج كـ HTML
        $htmlView = view("web-views.checkout.partials.company_shipping_oto", [
            "rate" => $rate,
            "originCity" => $originCity,
            "destinationCity" => $destinationCity,
            "totalWeight" => $totalWeight,
            "products" => $cartItems,
            "shop_id" => $shop_id
        ])->render();
    
        return response($htmlView);
    }
    
    
}