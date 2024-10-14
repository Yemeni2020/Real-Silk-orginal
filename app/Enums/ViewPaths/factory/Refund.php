<?php

namespace App\Enums\ViewPaths\factory;

class Refund
{
    const INDEX =[
        URI => 'index',
        VIEW => 'factory-views.refund.index',
    ];
    const DETAILS =[
        URI => 'details',
        VIEW => 'factory-views.refund.details'
    ];
    const UPDATE_STATUS =[
        URI => 'update-status',
        VIEW => ''
    ];
}
