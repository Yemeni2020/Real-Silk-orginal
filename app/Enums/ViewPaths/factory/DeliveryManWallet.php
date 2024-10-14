<?php

namespace App\Enums\ViewPaths\factory;

enum DeliveryManWallet
{
    const INDEX = [
        URI => 'index',
        VIEW => 'factory-views.delivery-man.wallet.index',
        ROUTE => 'factory.delivery-man.withdraw.index',
    ];
    const ORDER_HISTORY = [
        URI => 'order-history',
        VIEW => 'factory-views.delivery-man.wallet.order-history'
    ];
    const ORDER_STATUS_HISTORY = [
        URI => 'order-history-status',
        VIEW => 'factory-views.delivery-man.wallet._order-status-history'
    ];
    const EARNING = [
        URI => 'earning',
        VIEW => 'factory-views.delivery-man.wallet.earning'
    ];
    const CASH_COLLECT = [
        URI => 'cash-collect',
        VIEW => 'factory-views.delivery-man.wallet.cash-collect'
    ];
}
