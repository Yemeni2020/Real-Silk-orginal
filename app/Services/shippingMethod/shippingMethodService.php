<?php
namespace App\Services\shippingMethod;

use App\Services\shippingMethod\OtoShippingMethod;
use App\Traits\FileManagerTrait;

class shippingMethodService{
    public function Show_ShippingMethod($shippingMethod,$shop_id){
        if(isset($shippingMethod["title"])){
            $Method = match(strtolower($shippingMethod->title)) {
                'oto' => new OtoShippingMethod(),
            };

            // $cart=$shop_id;

            return $Method->getApi($shop_id);
        }
    }
}