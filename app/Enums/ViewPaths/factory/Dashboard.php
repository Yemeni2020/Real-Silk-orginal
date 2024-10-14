<?php

namespace App\Enums\ViewPaths\factory;

enum Dashboard
{
    const INDEX = [
        URI => '/',
        VIEW => 'factory-views.dashboard.index',
        ROUTE => 'factory.dashboard.index'
    ];

    const ORDER_STATUS = [
        URI => 'order-status',
        VIEW => 'factory-views.partials._dashboard-order-status'
    ];
    const EARNING_STATISTICS = [
        URI => 'earning-statistics',
        VIEW => ''
    ];
    const WITHDRAW_REQUEST = [
            URI => 'withdraw-request',
        VIEW => ''
    ];
    const METHOD_LIST = [
        URI => 'method-list',
        VIEW => ''
    ];

}
