<?php

namespace App\Enums\ViewPaths\factory;

enum ForgotPassword
{
    const INDEX = [
      URI => 'index',
      VIEW => 'factory-views.auth.forgot-password.index'
    ];
    const OTP_VERIFICATION = [
      URI => 'otp-verification',
      VIEW => 'factory-views.auth.forgot-password.verify-otp-view'
    ];
    const RESET_PASSWORD = [
        URI => 'reset-password',
        URL => 'factory/auth/forgot-password/reset-password',
        ROUTE =>'factory.auth.reset-password',
        VIEW => 'factory-views.auth.forgot-password.reset-password-view'
    ];

}
