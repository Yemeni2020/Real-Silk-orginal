<?php

namespace App\Enums\ViewPaths\factory;

enum DeliveryManWithdraw
{
    const INDEX = [
        URI => 'index',
        VIEW => 'factory-views.delivery-man.withdraw.index',
        TABLE_VIEW=>'factory-views.delivery-man.withdraw._table'
    ];
    const DETAILS = [
        URI => 'details',
        VIEW => 'factory-views.delivery-man.withdraw.details'
    ];
    const UPDATE_STATUS = [
        URI => 'update-status',
        VIEW => ''
    ];
    const EXPORT = [
        URI => 'export',
        VIEW => '',
        FILE_NAME => 'Delivery-Man-Withdraw-Request.xlsx'
    ];


}
