<?php

namespace App\Enums\ViewPaths\factory;

enum ShippingMethod
{

    const INDEX = [
        URI => 'index',
        VIEW => 'factory-views.shipping-method.index',
        ROUTE =>'factory.business-settings.shipping-method.index'
    ];
    const UPDATE = [
        URI => 'update',
        VIEW => 'factory-views.shipping-method.update-view'
    ];
    const UPDATE_STATUS = [
        URI => 'update-status',
        VIEW => 'factory-views.shipping-method.update-view'
    ];
    const DELETE = [
        URI => 'delete',
        VIEW => ''
    ];
}
