<?php

namespace App\Providers;

use App\Apis\AuthApiService;
use App\Apis\ResourceApiService;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind(
            AuthApiService::class,
            fn($app) => new AuthApiService(),
        );

        $this->app->bind(
            ResourceApiService::class,
            fn($app) => new ResourceApiService(),
        );
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
