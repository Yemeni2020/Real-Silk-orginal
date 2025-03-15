<?php

namespace App\Enums\ViewPaths\Office;

enum Office
{
    const INDEX = [
        URI => 'index',
        VIEW => 'office-views.info.index',
        ROUTE => 'vendor.shop.index',
    ];
    const ORDER_SETTINGS = [
        URI => 'update-order-settings',
        VIEW => 'vendor-views.shop.order-settings-view'
    ];
    const UPDATE = [
        URI => 'update',
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
