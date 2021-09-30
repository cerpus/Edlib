<?php

namespace App\Providers;

use App\Apis\AuthApiService;
use App\Apis\ContentAuthorService;
use App\Apis\LtiApiService;
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
            ContentAuthorService::class,
            fn($app) => new ContentAuthorService(config('services.contentAuthor.url'), config('services.contentAuthor.internalKey')),
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
