<?php

namespace App\Enums\ViewPaths\Admin;

enum ReferralVendorSettings
{
    const VIEW = [
        URI => '/',
        VIEW => 'admin-views.business-settings.referral-vendor.index'
    ];
    const List = [
        URI => 'get-list/',
        VIEW => 'admin-views.business-settings.referral-vendor.vendor-list'
    ];

}
