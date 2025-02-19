<?php

namespace App\Enums\ViewPaths\Admin;

enum Office
{
    const LIST = [
        URI => 'list',
        VIEW => 'admin-views.office.index'
    ];

    const ADD = [
        URI => 'add',
        VIEW => 'admin-views.office.add-new-office'
    ];

    const ORDER_LIST = [
        URI => 'order-list',
        VIEW => 'admin-views.office.order-list'
    ];
    const ORDER_LIST_EXPORT = [
        URI => 'order-list-export',
    ];
    const ORDER_DETAILS = [
        URI => 'order-details',
        VIEW => 'admin-views.office.order-details'
    ];

    const PRODUCT_LIST = [
        URI => 'product-list',
        VIEW => 'admin-views.office.product-list'
    ];

    const STATUS = [
        URI => 'status',
        VIEW => ''
    ];

    const EXPORT = [
        URI => 'export',
        VIEW => ''
    ];

    const VIEW = [
        URI => 'view',
        VIEW => 'admin-views.office.view'
    ];

    const VIEW_ORDER = [
        URI => '',
        VIEW => 'admin-views.office.view.order'
    ];

    const VIEW_PRODUCT = [
        URI => '',
        VIEW => 'admin-views.office.view.product'
    ];

    const VIEW_REVIEW = [
        URI => '',
        VIEW => 'admin-views.office.view.review'
    ];

    const VIEW_REFERRAL = [
        URI => '',
        VIEW => 'admin-views.office.view.referral'
    ];
    const VIEW_TRANSACTION = [
        URI => '',
        VIEW => 'admin-views.office.view.transaction'
    ];

    const VIEW_SETTING = [
        URI => '',
        VIEW => 'admin-views.office.view.setting'
    ];

    const UPDATE = [
        URI => 'update',
        VIEW => 'admin-views.employee.edit'
    ];
    const UPDATE_SETTING = [
        URI => 'update_setting',
        VIEW => ''
    ];

    const SALES_COMMISSION_UPDATE = [
        URI => 'sales-commission-update',
        VIEW => ''
    ];

    const WITHDRAW_LIST = [
        URI => 'withdraw-list',
        VIEW => 'admin-views.office.withdraw'
    ];

    const WITHDRAW_LIST_EXPORT = [
        URI => 'withdraw-list-export-excel',
        VIEW => ''
    ];

    const WITHDRAW_VIEW = [
        URI => 'withdraw-view',
        VIEW => 'admin-views.office.withdraw-view',
    ];

    const WITHDRAW_STATUS = [
        URI => 'withdraw-status',
        VIEW => ''
    ];


}
