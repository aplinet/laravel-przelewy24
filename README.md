# Przelewy24 payment processor for Laravel
Laravel package which provides Przelewy24 payment processor support.

## Installation
1. Install composer package using command:
```
composer require lukasz-adamski/laravel-przelewy24
```

2. Add Service Provider in `config/app.php`:
```php
Adams\Przelewy24\Przelewy24ServiceProvider::class,
```

3. Add Facade in `config/app.php`:
```php
'Przelewy24' => Adams\Przelewy24\Facades\Facade::class,
```

4. Publish configuration file to your project:
```php
php artisan vendor:publish --provider="Adams\Przelewy24\Przelewy24ServiceProvider"
```

## Environment
You can setup these environment variables to configure Przelewy24 API access:
- `PRZELEWY24_MODE` - Current API mode, by default this value is set to `sandbox` to test implementation. On production you need to set this value to `live`,
- `PRZELEWY24_MERCHANT_ID` - Your merchant identifier received from payment provider,
- `PRZELEWY24_POS_ID` - Shop identifier to process payments. If you don't want to process incoming payments to given shop enter here merchant identifier and payments will not be classified to any shop,
- `PRZELEWY24_CRC` - Random string received from payment provider to sign API requests.

## Testing
To run predefined test set use:
```bash
php vendor/bin/phpunit
```

## Usage
Below you have example controller implementation:
```php
<?php

namespace App\Http\Controllers;

use Przelewy24;
use Adams\Przelewy24\Transaction;
use App\Http\Controllers\Controller;
use Illuminate\Support\Str;

class ExampleController extends Controller
{
    /**
     * Redirect user to payment provider.
     *
     * @return Response
     */
    public function pay()
    {
        $payload = new Transaction();
        $payload->setSessionId(Str::random(30));
        $payload->setAmount($item->price * 100);
        $payload->setDescription('My item description');
        $payload->setEmail('customer@example.com');
        $payload->setUrlReturn(url('/'));

        return Przelewy24::redirect($payload);
    }
}
```

## Events
When transaction receiver from this package is enabled (`disable_package_routes` setting) you can listen for predefined events dispatched by default webhook controller:
- `\Adams\Przelewy24\Events\TransactionReceived::class` - transaction was successfully received (signature is valid). If you don't use automatic verification you need to do it manually to charge prepaid money,
- `\Adams\Przelewy24\Events\TransactionVerified::class` - transaction was successfully received and verified via provider's API. After dispatching this event, prepaid money is already added to your account.

### Example
1. Below you can see example code of `TransactionVerified` event listener created by `php artisan make:listener ReceivePayment`:
```php
<?php

namespace App\Listeners;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Adams\Przelewy24\Events\TransactionVerified;

class ReceivePayment
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param TransactionVerified $event
     * @return void
     */
    public function handle(TransactionVerified $event)
    {
        //
    }
}
```

2. You also need to register new listener in `app/Providers/EventServiceProvider.php` file.
```php
/**
 * The event listener mappings for the application.
 *
 * @var array
 */
protected $listen = [
    \Adams\Przelewy24\Events\TransactionVerified::class => [
        \App\Listeners\ReceivePayment::class,
    ],
];
```