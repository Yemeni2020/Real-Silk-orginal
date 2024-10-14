<?php

namespace App\Enums\ViewPaths\factory;

enum Profile
{
    const INDEX = [
        URI => 'index',
        VIEW => 'factory-views.profile.index',
        ROUTE => 'factory.profile.index'
    ];
    const UPDATE = [
        URI => 'update',
        VIEW => 'factory-views.profile.update-view'
    ];
    const BANK_INFO_UPDATE = [
        URI => 'update-bank-info',
        VIEW => 'factory-views.profile.bank-info-update-view'
    ];
}
