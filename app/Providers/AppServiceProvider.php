<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\MqttService;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton('mqtt', function ($app) {
            return new MqttService();
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
