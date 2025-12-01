<?php

namespace SantosDave\Paystack;

use Illuminate\Support\ServiceProvider;
use SantosDave\Paystack\Http\Client;


class PaystackServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../Config/paystack.php' => config_path('paystack.php'),
            ], 'paystack-config');

            $this->publishes([
                __DIR__ . '/../database/migrations' => database_path('migrations'),
            ], 'paystack-migrations');
        }

        $this->mergeConfigFrom(
            __DIR__ . '/Config/paystack.php',
            'paystack'
        );
    }

    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton('paystack.client', function ($app) {
            return new Client($app['config']['paystack']);
        });

        $this->app->singleton('paystack', function ($app) {
            return new Paystack($app['paystack.client']);
        });

        $this->app->alias('paystack', Paystack::class);
    }

    /**
     * Get the services provided by the provider.
     */
    public function provides(): array
    {
        return [
            'paystack',
            'paystack.client',
            Paystack::class,
        ];
    }
}