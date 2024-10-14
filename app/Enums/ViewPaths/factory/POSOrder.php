<?php

namespace App\Enums\ViewPaths\factory;

enum POSOrder
{
    const ORDER_DETAILS = [
        URI => 'order-details',
        VIEW => 'factory-views.pos.order.order-details',
    ];
    const ORDER_PLACE = [
      VIEW => 'factory-views.pos.order.order-details',
      URI => 'order-place'

    ];
    const CANCEL_ORDER =[
        VIEW => 'factory-views.pos.partials._view-hold-orders',
        URI => 'cancel-order',
    ];
    const HOLD_ORDERS =[
        VIEW => 'factory-views.pos.partials._view-hold-orders',
        URI => 'view-hold-orders',

    ];
}
