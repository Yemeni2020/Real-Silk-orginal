<?php

namespace App\Enums\ViewPaths\factory;

enum Product
{
    const ADD = [
        URI => 'add',
        VIEW => 'factory-views.product.add-new'
    ];

    const LIST = [
        URI => 'list',
        VIEW => 'factory-views.product.list'
    ];

    const UPDATE = [
        URI => 'update',
        VIEW => 'factory-views.product.edit'
    ];

    const VIEW = [
        URI => 'view',
        VIEW => 'factory-views.product.view'
    ];

    const SKU_COMBINATION = [
        URI => 'sku-combination',
        VIEW => ''
    ];

    const UPDATE_STATUS = [
        URI => 'status-update',
        VIEW => ''
    ];

    const GET_CATEGORIES = [
        URI => 'get-categories',
        VIEW => ''
    ];

    const BARCODE_VIEW = [
        URI => 'barcode',
        VIEW => 'factory-views.product.barcode'
    ];

    const BARCODE_GENERATE = [
        URI => 'barcode',
        VIEW => ''
    ];

    const EXPORT_EXCEL = [
        URI => 'export-excel',
        VIEW => ''
    ];

    const STOCK_LIMIT = [
        URI => 'stock-limit-list',
        VIEW => 'factory-views.product.stock-limit-list'
    ];

    const DELETE = [
        URI => 'delete',
        VIEW => ''
    ];

    const DELETE_IMAGE = [
        URI => 'delete-image',
        VIEW => ''
    ];

    const GET_VARIATIONS = [
        URI => 'get-variations',
        VIEW => 'factory-views.product.partials._update_stock'
    ];

    const UPDATE_QUANTITY = [
        URI => 'update-quantity',
        VIEW => ''
    ];

    const BULK_IMPORT = [
        URI => 'bulk-import',
        VIEW => 'factory-views.product.bulk-import'
    ];
    const SEARCH = [
        URI => 'search',
        VIEW => 'factory-views.partials._search-product'

    ];
}
