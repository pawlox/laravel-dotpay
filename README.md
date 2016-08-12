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
        'email'             => "Customer@Email",
        'button'            => "Pay Now",
    ];
    
    $form = Dotpay::createForm($data);
    
    return view('dotpay.payment', [
        'form' => $form
    ]);
```

### In your view type something like ###
```php
    @if (isset($form) && $form)
        {!! $form !!}
    @endif
```

### Will be rendered ###

```html
    <form class="dotpay-form" action="https://ssl.dotpay.pl/test_payment/" method="POST">
        <input type="hidden" name="id" value="XXXXX"/>
        <input type="hidden" name="description" value="Description"/>
        <input type="hidden" name="channel" value="73"/>
        <input type="hidden" name="api_version" value="dev"/>
        <input type="hidden" name="lang" value="pl"/>
        <input type="hidden" name="control" value="SOME RANDOM STRING HERE - ORDER TOKEN ETC"/>
        <input type="hidden" name="amount" value="9999"/>
        <input type="hidden" name="type" value="4"/>
        <input type="hidden" name="firstname" value="CustomerName"/>
        <input type="hidden" name="lastname" value="CustomerSurname"/>
        <input type="hidden" name="email" value="Customer@Email"/>
        <input type="hidden" name="p_email" value="YOUR@EMAIL.COM"/>
        <input type="hidden" name="p_info" value="TEST STORE"/>
        <input type="hidden" name="URL" value="XXXX"/>  <!-- url generate from route named 'dotpay.success' -->
        <input type="hidden" name="URLC" value="XXXXX"/> <!-- url generate from route named 'dotpay.notification' -->
        <input type="hidden" name="bylaw" value="1"/>
        <input type="hidden" name="personal_data" value="1"/>
        <button class="dotpay-from-submit" type="submit">Pay Now</button>
    </form>
```

### Create auto submitted form ###

```js
    <script type="text/javascript">
        window.onload = function () {
            var form = document.getElementsByClassName("dotpay-form")[0];
            form.submit();
        };
    </script>
```

### Handle action for URLC notification ###

```php
    public function notification(Request $request)
    {
        $data = $request->all();
        $ip = $request->getClientIp();
        
        // additional callback when validation fails
        Dotpay::failed(function ($data) {
            Log::debug("DOTPAY FAILED");
            Log::debug("DATA" . json_encode($data, JSON_PRETTY_PRINT));
            
            // do some stuff when data verification fails (hash is invalid)
        });

        // additional callback when validation passes
        Dotpay::success(function ($data) {
            Log::debug("DOTPAY SUCCESS");
            Log::debug("DATA" . json_encode($data, JSON_PRETTY_PRINT));
            
             // do some stuff when data verification passes
             // compare amount, control field etc. 
        });


        // validate request IP and hash
        if (Dotpay::validateIP($ip)) {
            if (Dotpay::validate($data)) {
                Log::debug("Request is valid");
            } else {
                Log::debug("Request is invalid");
            }  
        } else {
            Log::debug("Request IP is INVALID: {$ip}");
        }

        return "OK";
    }
```

