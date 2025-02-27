<?php

namespace App\Enums\ViewPaths\Admin;

enum AIConfig
{
    const VIEW = [
        URI => 'view',
        VIEW => 'admin-views.business-settings.ai-config.view'
    ];

    const UPDATE = [
        URI => 'update',
        VIEW => ''
    ];

    const UPDATE_SETTING_Translate = [
        URI => 'update-translate',
        VIEW => ''
    ];
    const UPDATE_SETTING_GENERATE = [
        URI => 'update-setting-generate',
        VIEW => ''
    ];

}
