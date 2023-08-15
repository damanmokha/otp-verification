<?php

namespace Damanmokha\OtpVerification;

use Illuminate\Support\ServiceProvider;

class OtpVerificationServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
    }

    public function register()
    {
        //publishing migrations
        $this->publishes([
            __DIR__.'/../database/migrations' => database_path('migrations'),
        ], 'otp-verfication-migrations');

        $this->app->singleton(OtpVerficaition::class, function () {
            return new OtpVerficaition();
        });
    }
}
