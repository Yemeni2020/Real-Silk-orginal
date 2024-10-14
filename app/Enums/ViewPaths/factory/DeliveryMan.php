<?php

namespace App\Enums\ViewPaths\factory;

enum DeliveryMan
{
    const INDEX = [
        URI => 'index',
        VIEW => 'factory-views.delivery-man.index'
    ];
    const LIST = [
        URI => 'list',
        VIEW => 'factory-views.delivery-man.list',
        ROUTE => 'factory.delivery-man.list'
    ];
    const UPDATE = [
        URI => 'update',
        VIEW => 'factory-views.delivery-man.update-view'
    ];
    const UPDATE_STATUS = [
        URI => 'update-status',
        VIEW => ''
    ];
    const DELETE = [
        URI => 'delete',
        VIEW => ''
    ];
    const RATING = [
        URI => 'rating',
        VIEW => 'factory-views.delivery-man.rating'
    ];
}
