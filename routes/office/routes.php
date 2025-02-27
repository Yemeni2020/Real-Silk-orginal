<?php

use App\Enums\ViewPaths\Office\Auth;
use App\Enums\ViewPaths\Vendor\ForgotPassword;
use App\Http\Controllers\Vendor\Auth\ForgotPasswordController;
use App\Http\Controllers\Office\Auth\LoginController;
use Illuminate\Support\Facades\Route;


Route::group(['middleware' => ['maintenance_mode']], function () {

    Route::group(['prefix' => 'office', 'as' => 'office.'], function () {
        /* authentication */
        Route::group(['prefix' => 'auth', 'as' => 'auth.'], function () {
            Route::controller(LoginController::class)->group(function () {
                Route::get(Auth::OFFICE_LOGIN[URI], 'getLoginView');
                Route::get(Auth::RECAPTURE[URI] . '/{tmp}', 'generateReCaptcha')->name('recaptcha');
                Route::post(Auth::OFFICE_LOGIN[URI], 'login')->name('login');
                Route::get(Auth::OFFICE_LOGOUT[URI], 'logout')->name('logout');
            });
            Route::group(['prefix' => 'forgot-password', 'as' => 'forgot-password.'], function () {
                Route::controller(ForgotPasswordController::class)->group(function () {
                    Route::get(ForgotPassword::INDEX[URI], 'index')->name('index');
                    Route::post(ForgotPassword::INDEX[URI], 'getPasswordResetRequest');
                    Route::get(ForgotPassword::OTP_VERIFICATION[URI], 'getOTPVerificationView')->name('otp-verification');
                    Route::post(ForgotPassword::OTP_VERIFICATION[URI], 'submitOTPVerificationCode');
                    Route::get(ForgotPassword::RESET_PASSWORD[URI], 'getPasswordResetView')->name('reset-password');
                    Route::post(ForgotPassword::RESET_PASSWORD[URI], 'resetPassword');
                });
            });

        });
        /* end authentication */
        
    });

});
