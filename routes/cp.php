<?php

use Illuminate\Support\Facades\Route;

Route::namespace('\rogergrant99\FaviconGenerator\Http\Controllers\Cp')
    ->prefix('favicon-generator/')
    ->name('rogergrant99.favicon-generator.')
    ->group(function () {
        Route::get('/', 'FaviconController@index')->name('index');
        Route::post('/update', 'FaviconController@update')->name('update');
    });