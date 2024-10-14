<?php

namespace App\Enums\ViewPaths\factory;

enum POS
{
    const SUMMARY =[
        VIEW => 'factory-views.pos.partials._cart-summary',
    ];
    const CART =[
        VIEW => 'factory-views.pos.partials._cart',
    ];
    const INDEX =[
        URI => '/',
        VIEW => 'factory-views.pos.index',
        ROUTE => 'factory.pos.index',
    ];

    const CHANGE_CUSTOMER =[
        URI => 'change-customer',
    ];
    const UPDATE_DISCOUNT =[
        URI => 'update-discount',
    ];
    const COUPON_DISCOUNT =[
        URI => 'coupon-discount',
    ];
    const STORE_KEY =[
        URI => 'store-key',
    ];
    const QUICK_VIEW = [
        URI => 'quick-view',
        VIEW => 'factory-views.pos.partials._quick-view'
    ];
    const SEARCH = [
        URI => 'search-product',
        VIEW => 'factory-views.pos.partials._search-product'
    ];
}
