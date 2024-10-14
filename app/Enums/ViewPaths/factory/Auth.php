<?php

namespace App\Enums\ViewPaths\factory;

enum Auth
{
    const factory_LOGIN = [
        URI => 'login',
        VIEW => 'factory-views.auth.login',
    ];

    const factory_LOGOUT = [
        URI => 'factory.auth.login',
        VIEW => 'factory-views.auth.login'
    ];
    const RECAPTURE = [
        URI => 'recaptcha',
    ];


}
