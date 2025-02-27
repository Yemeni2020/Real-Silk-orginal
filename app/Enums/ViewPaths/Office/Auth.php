<?php

namespace App\Enums\ViewPaths\Office;

enum Auth
{
    const OFFICE_LOGIN = [
        URI => 'login',
        VIEW => 'office-views.auth.login',
    ];

    const OFFICE_LOGOUT = [
        URI => 'office.auth.login',
        VIEW => 'office-views.auth.login'
    ];
    const RECAPTURE = [
        URI => 'recaptcha',
    ];
    const OFFICE_REGISTRATION = [
        URI => 'index',
        VIEW => 'seller_registration'
    ];
    const OFFICE_STEP1 = [
        URI => 'step1',
    ];


}
