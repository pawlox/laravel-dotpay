<?php

namespace Alzo\LaravelDotpay;


class ServiceProvider extends \Illuminate\Support\ServiceProvider
{
    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $configPath = __DIR__ . '/../config/dotpay.php';
        $this->mergeConfigFrom($configPath, 'dotpay');

        $this->app->bind('dotpay', function ($app) {
            return new LaravelDotpay($app);
        });

        $this->app->alias('dotpay', 'Alzo\LaravelDotpay\LaravelDotpay');
        $this->publishes([$configPath => $this->getConfigPath()], 'config');
    }

    /**
     * Get the config path
     *
     * @return string
     */
    protected function getConfigPath()
    {
        return config_path('dotpay.php');
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return array('dotpay');
    }
}