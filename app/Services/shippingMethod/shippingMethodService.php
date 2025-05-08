<?php
namespace App\Services\shippingMethod;

use App\Services\shippingMethod\OtoShippingMethod;
use App\Traits\FileManagerTrait;

class shippingMethodService{
    public function Show_ShippingMethod($shippingMethod){
        if(isset($shippingMethod["title"])){
            $Method = match(strtolower($shippingMethod->title)) {
                'oto' => new OtoShippingMethod(),
            };

            $cart=\App\Models\Cart::whereHas('product', function ($query) {
                return $query->active();
            })->where(['customer_id' => (auth('customer')->check() ? auth('customer')->id() : session('guest_id'))])
            ->get()->groupBy('cart_group_id');

            return $Method->getApi($cart);
        }
    }
}