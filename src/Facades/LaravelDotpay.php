<?php

namespace Alzo\LaravelDotpay\Facades;

use Illuminate\Support\Facades\Facade;

class LaravelDotpay extends Facade
{
    public static function getFacadeAccessor()
    {
        return "laravel-dotpay";
    }
}