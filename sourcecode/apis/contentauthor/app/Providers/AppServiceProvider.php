<?php

namespace App\Providers;

use App\Apis\AuthApiService;
use App\Apis\ResourceApiService;
use App\EdlibResource\Oauth1Credentials;
use App\H5POption;
use App\Http\Middleware\AddExtQuestionSetToRequestMiddleware;
use App\Http\Middleware\RequestId;
use App\Libraries\ContentAuthorStorage;
use App\Libraries\H5P\Helper\H5POptionsCache;
use App\Observers\H5POptionObserver;
use Cerpus\EdlibResourceKit\Oauth1\CredentialStoreInterface;
use Illuminate\Log\Logger;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Paginator::useBootstrapThree();

        H5POption::observe(H5POptionObserver::class);
        //
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        if ($this->app->environment() !== 'production') {
            $this->app->register(\Barryvdh\LaravelIdeHelper\IdeHelperServiceProvider::class);
        }

        $this->app->when(Oauth1Credentials::class)
            ->needs('$consumerKey')
            ->giveConfig('app.consumer-key');

        $this->app->when(Oauth1Credentials::class)
            ->needs('$consumerSecret')
            ->giveConfig('app.consumer-secret');

        $this->app->singleton(CredentialStoreInterface::class, Oauth1Credentials::class);

        $this->app->singleton(H5POptionsCache::class, function () {
            return new H5POptionsCache();
        });

        $this->app->singleton(ContentAuthorStorage::class, function () {
            return new ContentAuthorStorage(config('app.cdnPrefix'));
        });

        $this->app
            ->when(AddExtQuestionSetToRequestMiddleware::class)
            ->needs('$environment')
            ->giveConfig('app.env');

        $this->app
            ->when(AddExtQuestionSetToRequestMiddleware::class)
            ->needs('$enabled')
            ->giveConfig('feature.add-ext-question-set-to-request');

        $this->app->bind(
            ResourceApiService::class,
            function ($app) {
                return new ResourceApiService();
            }
        );

        $this->app->bind(
            AuthApiService::class,
            function ($app) {
                return new AuthApiService();
            }
        );

        $this->app->when(RequestId::class)
            ->needs(Logger::class)
            ->give(fn () => Log::channel());
    }
}
