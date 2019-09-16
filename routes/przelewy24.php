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

    Route::name('webhooks.przelewy24')->post(
        'webhooks/przelewy24',
        'WebhookController@handleWebhook'
    );

});
