<?php
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::group(['prefix' => 'vue'], function () {
    Route::get('/', function () {
        $categories = \App\Models\Category::with('childes')->get();

        return Inertia::render('Dashboard', [
            'categories' => $categories,
        ]);
    });
});
