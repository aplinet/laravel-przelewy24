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