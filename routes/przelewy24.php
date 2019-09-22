<?php

/*
|--------------------------------------------------------------------------
| Przelewy24 Routes
|--------------------------------------------------------------------------
|
| This file define all routes used by package to process incoming
| transactions from payment provider.
|
*/

Route::namespace('\Adams\Przelewy24\Http\Controllers')->group(function () {

    Route::name('webhook.przelewy24')->post(
        'webhook/przelewy24',
        'WebhookController@handle'
    );

});
