<?php

namespace App\Providers;

use App\Apis\AuthApiService;
use App\Apis\ResourceApiService;
use App\H5POption;
use App\Http\Middleware\SignedOauth10Request;
use App\Http\Requests\LTIRequest;
use App\Libraries\ContentAuthorStorage;
use App\Libraries\H5P\Helper\H5POptionsCache;
use App\Libraries\ImportOwner;
use App\Observers\H5POptionObserver;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Collection;
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

        if (!Collection::hasMacro('recursive')) {
            Collection::macro('recursive', function ($levels = 100) {
                return $this->map(function ($value) use ($levels) {
                    if ($levels > 0 && (is_array($value) || is_object($value))) {
                        return collect($value)->recursive(--$levels);
                    }
                    return $value;
                });
            });
        }

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

        $this->app
            ->when(SignedOauth10Request::class)
            ->needs('$consumerKey')
            ->giveConfig('app.consumer-key');

        $this->app
            ->when(SignedOauth10Request::class)
            ->needs('$consumerSecret')
            ->giveConfig('app.consumer-secret');

        $this->app->bind(ImportOwner::class, function ($app) {
            return new ImportOwner(config('ndla.userId'));
        });

        $this->app->singletonIf(LTIRequest::class, function () {
            $request = request();
            if ($request->input('lti_message_type')) {
                return new LTIRequest($request->url(), $request->all());
            }
        });

        $this->app->singleton(H5POptionsCache::class, function () {
            return new H5POptionsCache();
        });

        $this->app->singleton(ContentAuthorStorage::class, function () {
            return new ContentAuthorStorage(config('app.cdnPrefix'));
        });

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
    }
}
