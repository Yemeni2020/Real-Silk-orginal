<?php

namespace App\Enums\ViewPaths\Vendor;

enum Brand
{
    const LIST = [
        URI => 'list',
        VIEW => 'vendor-views.brand.list'
    ];
    const ADD = [
        URI => 'add-new',
        VIEW => 'vendor-views.brand.add-new'
    ];

    const UPDATE = [
        URI => 'update',
        VIEW => 'vendor-views.brand.edit'
    ];

    const DELETE = [
        URI => 'delete',
        VIEW => ''
    ];

    const STATUS = [
        URI => 'status-update',
        VIEW => ''
    ];

    const EXPORT = [
        URI => 'export',
        VIEW => ''
    ];
}
