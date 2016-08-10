<?php

namespace Alzo\Dotpay;

use Alzo\LaravelDotpay\LaravelDotpay;
use Illuminate\Routing\Router;

class ServiceProvider extends \Illuminate\Support\ServiceProvider
{
    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $configPath = __DIR__ . '/../config/laravel-dotpay.php';
        $this->mergeConfigFrom($configPath, 'laravel-dotpay');

        $this->app->singleton('laravel-dotpay', function ($app) {
            return new LaravelDotpay();
        });

        $this->app->alias('laravel-dotpay', 'Alzo\LaravelDotpay\LaravelDotpay');
    }
}