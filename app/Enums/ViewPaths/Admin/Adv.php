<?php

namespace App\Enums\ViewPaths\Admin;

enum Adv
{
    const LIST = [
        URI => 'list',
        VIEW => 'admin-views.Adv.view-Adv'
    ];

    const ADD = [
        URI => 'add',
        VIEW => ''
    ];

    const DELETE = [
        URI => 'delete',
        VIEW => ''
    ];

    const STATUS = [
        URI => 'status',
        VIEW => ''
    ];

    const UPDATE = [
        URI => 'update',
        VIEW => 'admin-views.Adv.Adv-edit',
        ROUTE => 'admin.banner.list'
    ];

    const LISTCATEGORY = [
        URI => 'category-list',
        VIEW => 'admin-views.Adv.view-category',
        ROUTE => 'admin.banner.list'
    ];

    const ADDCATEGORY = [
        URI => 'category-add',
        VIEW => 'admin-views.banner.edit',
        ROUTE => 'admin.banner.list'
    ];
    
    const UPDATECATEGORY = [
        URI => 'category-update',
        VIEW => 'admin-views.Adv.category-edit',
        ROUTE => 'admin.banner.list'
    ];

    const DELETECATEGORY = [
        URI => 'category-delete',
        VIEW => 'admin-views.banner.edit',
        ROUTE => 'admin.banner.list'
    ];
}
