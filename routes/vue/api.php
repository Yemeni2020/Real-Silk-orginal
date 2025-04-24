<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\VueApi\HomeController;
use App\Http\Controllers\VueApi\ProductListController;
use App\Http\Controllers\VueApi\ProductDetailsController;

Route::group(['prefix' => 'vueAPI'], function () {

    Route::get('/translate', function (Request $request) {
        $key = $request->query('key');

        if (!$key) {
            return response()->json([
                'error' => 'Missing translation key.'
            ], 400);
        }

        return response()->json([
            'translation' => translate($key)
        ]);
    });

    Route::get('/home_categoray', [HomeController::class, 'getHomeCategories']);
    Route::get('/menu_categories', [HomeController::class, 'getMenuData']);
    Route::get('/product_categories', [ProductListController::class, 'getProductCategories']);
    Route::get('/product/details/{id}', [ProductDetailsController::class, 'getDetails']);

});

?>