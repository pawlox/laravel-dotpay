<?php

namespace Alzo\LaravelDotpay;

class Facade extends \Illuminate\Support\Facades\Facade
{
    public static function getFacadeAccessor()
    {
        return "dotpay";
    }
}