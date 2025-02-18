<?php

namespace App\Enums\ViewPaths\Admin;

enum CurrencyConfig
{
    const VIEW = [
        URI => 'view',
        VIEW => 'admin-views.business-settings.currency-config.view'
    ];

    const UPDATE = [
        URI => 'update',
        VIEW => ''
    ];

    const APPLE_UPDATE = [
        URI => 'update-apple',
        VIEW => ''
    ];

}
