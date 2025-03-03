<?php

namespace App\Enums\ViewPaths\Office;

enum Chatting
{
    const INDEX = [
        URI => 'index',
        VIEW => 'office-views.chatting.index',
    ];
    const MESSAGE = [
        URI => 'message',
        VIEW => 'office-views.chatting.index',
    ];

    const NEW_NOTIFICATION = [
        URI => 'new-notification',
        VIEW => '',
    ];
}
