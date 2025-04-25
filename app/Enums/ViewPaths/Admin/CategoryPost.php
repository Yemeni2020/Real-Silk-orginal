<?php

namespace App\Enums\ViewPaths\Admin;

enum CategoryPost
{
    const LIST = [
        URI => 'view',
        VIEW => 'admin-views.post.category.view'
    ];
    const ADD = [
        URI => 'add-new',
        VIEW => 'admin-views.post.brand.add-new'
    ];
    const UPDATE = [
        URI => 'update/{id}',
        VIEW => 'admin-views.post.category.category-edit'
    ];
    const DELETE = [
        URI => 'delete',
        VIEW => ''
    ];
    const STATUS = [
        URI => 'status',
        VIEW => ''
    ];
    const EXPORT = [
        URI => 'export',
        VIEW => ''
    ];

}
