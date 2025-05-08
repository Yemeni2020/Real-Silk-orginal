<?php
namespace App\Services\shippingMethod\Contracts;

interface ShippingMethodInterface
{
    public function getApi($cart);
}