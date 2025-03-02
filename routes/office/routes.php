<?php

use App\Enums\ViewPaths\Office\Auth;
use App\Enums\ViewPaths\Vendor\ForgotPassword;
use App\Enums\ViewPaths\Office\Product;
use App\Http\Controllers\Vendor\Auth\ForgotPasswordController;
use App\Http\Controllers\Office\Auth\LoginController;
use App\Http\Controllers\Office\Product\ProductController;
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
        Route::group(['middleware' => ['seller']], function () {
            /* product */
            Route::group(['prefix' => 'products', 'as' => 'products.'], function () {
                Route::controller(ProductController::class)->group(function () {
                    Route::get(Product::LIST[URI] . '/{type}', 'index')->name('list');
                    Route::get(Product::ADD[URI], 'getAddView')->name('add');
                    Route::post(Product::ADD[URI], 'add');
                    Route::get(Product::GET_CATEGORIES[URI], 'getCategories')->name('get-categories');
                    Route::post(Product::SKU_COMBINATION[URI], 'getSkuCombinationView')->name('sku-combination');
                    Route::post(Product::DIGITAL_VARIATION_COMBINATION[URI], 'getDigitalVariationCombinationView')->name('digital-variation-combination');
                    Route::post(Product::DIGITAL_VARIATION_FILE_DELETE[URI], 'deleteDigitalVariationFile')->name('digital-variation-file-delete');
                    Route::post(Product::UPDATE_STATUS[URI], 'updateStatus')->name('status-update');
                    Route::get(Product::EXPORT_EXCEL[URI] . '/{type}', 'exportList')->name('export-excel');
                    Route::get(Product::VIEW[URI] . '/{id}', 'getView')->name('view');
                    Route::get(Product::BARCODE_VIEW[URI] . '/{id}', 'getBarcodeView')->name('barcode');
                    Route::delete(Product::DELETE[URI] . '/{id}', 'delete')->name('delete');
                    Route::get(Product::STOCK_LIMIT[URI], 'getStockLimitListView')->name('stock-limit-list');
                    Route::post(Product::UPDATE_QUANTITY[URI], 'updateQuantity')->name('update-quantity');
                    Route::get(Product::UPDATE[URI] . '/{id}', 'getUpdateView')->name('update');
                    Route::post(Product::UPDATE[URI] . '/{id}', 'update');
                    Route::get(Product::DELETE_IMAGE[URI], 'deleteImage')->name('delete-image');
                    Route::get(Product::GET_VARIATIONS[URI], 'getVariations')->name('get-variations');
                    Route::get(Product::BULK_IMPORT[URI], 'getBulkImportView')->name('bulk-import');
                    Route::post(Product::BULK_IMPORT[URI], 'importBulkProduct');
                    Route::get(Product::SEARCH[URI], 'getSearchedProductsView')->name('search-product');
                    Route::get(Product::PRODUCT_GALLERY[URI], 'getProductGalleryView')->name('product-gallery');
                    Route::get(Product::STOCK_LIMIT_STATUS[URI], 'getStockLimitStatus')->name('stock-limit-status');
                    Route::post(Product::DELETE_PREVIEW_FILE[URI], 'deletePreviewFile')->name('delete-preview-file');
                });
            });
        });
    });

});
