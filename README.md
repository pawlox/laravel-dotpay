# Laravel Dotpay Wrapper #

## Installation

Require this package with composer:

```shell
composer require alzo02/laravel-dotpay
```

After updating composer, add the ServiceProvider to the providers array in config/app.php

### Laravel 5.x:

```php
Alzo\LaravelDotpay\ServiceProvider::class,
```

If you want to use the facade, add this to your facades in app.php:

```php
'Dotpay' => Alzo\LaravelDotpay\Facade::class
```

Configure .env file, add these lines

```shell
DOTPAY_ID=XXXXXX
```

```shell
DOTPAY_EMAIL=YOUR@EMAIL.COM
```

```shell
DOTPAY_INFO="STORE INFO"
```

```shell
DOTPAY_PIN=YOUR_DOTPAY_PIN
```

## Usage

### Create form in controller ###

```php
    $data = [
        'description'       => "Description",
        'control'           => "SOME RANDOM STRING HERE - ORDER TOKEN ETC",
        'channel'           => 73,
        'amount'            => 9999,
        'firstname'         => "CustomerName",
        'lastname'          => "CustomerSurname",
        'email'             => "Customer@Email"
    ];
    
    $form = Dotpay::createForm($data);
    
    return view('dotpay.payment', [
        'form' => $form
    ]);
```

### Handle action for URLC notification ###

```php
    public function notification(Request $request)
    {
        $data = $request->all();
        $ip = $request->getClientIp();
        
        \Log::debug("DOTPAY REQUEST: {$ip}");

        Dotpay::failed(function ($data) {
            \Log::debug("DOTPAY FAILED");
            \Log::debug("DATA" . json_encode($data, JSON_PRETTY_PRINT));
        });

        Dotpay::success(function ($data) {
            \Log::debug("DOTPAY SUCCESS");
            \Log::debug("DATA" . json_encode($data, JSON_PRETTY_PRINT));
        });

        if (Dotpay::validateIP($ip)) {
            \Log::debug("IP is VALID: {$ip}");
        } else {
            \Log::debug("IP is INVALID: {$ip}");
        }

        if (Dotpay::validate($data)) {
            \Log::debug("success validation");
        } else {
            \Log::debug("failed validation");
        }

        return "OK";
    }
```

