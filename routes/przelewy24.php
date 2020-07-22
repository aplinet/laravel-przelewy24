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

$this->app->router->group(['namespace' => '\Adams\Przelewy24\Http\Controllers'], function () {
    $this->app->router->post(
        'webhook/przelewy24',
        ['as' => 'webhook.przelewy24', 'uses' => 'WebhookController@handle']
    );
});
