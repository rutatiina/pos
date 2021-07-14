<?php

Route::group(['middleware' => ['web', 'auth', 'tenant', 'service.accounting']], function() {

	Route::prefix('pos')->group(function ()
    {
        Route::get('orders', 'Rutatiina\POS\Http\Controllers\POSController@orders');
        Route::get('orders/{id}', 'Rutatiina\POS\Http\Controllers\POSController@show');
    });

    Route::resource('pos', 'Rutatiina\POS\Http\Controllers\POSController');

});
