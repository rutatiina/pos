<?php

Route::group(['middleware' => ['web', 'auth', 'tenant', 'service.accounting']], function() {

	Route::prefix('pos')->group(function ()
    {
        Route::post('routes', 'Rutatiina\POS\Http\Controllers\POSController@routes')->name('pos.routes');
        Route::get('orders', 'Rutatiina\POS\Http\Controllers\POSController@orders');
        Route::get('orders/{id}', 'Rutatiina\POS\Http\Controllers\POSController@show');
        Route::delete('delete', 'Rutatiina\POS\Http\Controllers\POSController@delete')->name('pos.delete');
        Route::patch('cancel', 'Rutatiina\POS\Http\Controllers\POSController@cancel')->name('pos.cancel');
    });

    Route::resource('pos', 'Rutatiina\POS\Http\Controllers\POSController');

});
