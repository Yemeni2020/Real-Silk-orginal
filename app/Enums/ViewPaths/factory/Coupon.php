<?php

namespace App\Enums\ViewPaths\factory;

enum Coupon
{
    const INDEX = [
        URI => 'index',
        VIEW => 'factory-views.coupon.index',
        ROUTE => 'factory.coupon.index'
    ];
    const ADD = [
        URI => 'add',
        VIEW => ''
    ];
    const UPDATE = [
        URI => 'update',
        VIEW => 'factory-views.coupon.update-view'
    ];
    const DELETE = [
        URI => 'delete',
        VIEW => ''
    ];
    const QUICK_VIEW = [
        URI => 'quick-view',
        VIEW => 'factory-views.coupon.quick-view'
    ];
    const UPDATE_STATUS = [
        URI => 'update-status',
        VIEW => ''
    ];

}
