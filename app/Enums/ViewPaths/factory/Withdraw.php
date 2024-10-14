<?php

namespace App\Enums\ViewPaths\factory;

enum Withdraw
{
    const INDEX = [
        URI => 'index',
        VIEW => 'factory-views.withdraw.index',
        TABLE_VIEW => 'factory-views.withdraw._table',
    ];
    const CLOSE_REQUEST = [
        URI => 'close',
        VIEW => ''
    ];

}
