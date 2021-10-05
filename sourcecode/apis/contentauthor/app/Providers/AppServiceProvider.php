<?php

namespace App\Providers;

use App\Events\ContentCreated;
use App\H5POption;
use App\Http\Requests\LTIRequest;
use App\Libraries\H5P\Helper\H5POptionsCache;
use App\Libraries\ImportOwner;
use App\Observers\H5POptionObserver;
use Cerpus\Helper\Clients\Client;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Event;
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
    }
}
