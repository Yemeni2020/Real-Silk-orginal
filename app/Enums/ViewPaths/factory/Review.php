<?php

namespace App\Enums\ViewPaths\factory;

enum Review
{
    const INDEX = [
        URI => 'index',
        VIEW => 'factory-views.reviews.index'
    ];
    const UPDATE_STATUS = [
        URI => 'update-status',
        VIEW => ''
    ];
    const EXPORT = [
        URI => 'export',
        VIEW => ''
    ];
}
