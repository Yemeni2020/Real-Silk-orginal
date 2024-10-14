<?php

namespace App\Enums\ViewPaths\factory;

enum Shop
{
    const INDEX = [
        URI => 'index',
        VIEW => 'factory-views.shop.index',
        ROUTE => 'factory.shop.index',
    ];
    const ORDER_SETTINGS = [
        URI => 'update-order-settings',
        VIEW => 'factory-views.shop.order-settings-view'
    ];
    const UPDATE = [
        URI => 'update',
        VIEW => 'factory-views.shop.update-view'
    ];
    const VACATION = [
        URI => 'add-vacation',
        VIEW => ''
    ];
    const TEMPORARY_CLOSE = [
        URI => 'close-shop-temporary',
        VIEW => ''
    ];
}
