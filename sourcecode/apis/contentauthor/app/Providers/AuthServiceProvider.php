<?php

namespace App\Providers;

use App\Auth\Guards\EdlibGuard;
use Illuminate\Container\Container;
use Illuminate\Contracts\Auth\Access\Gate as GateContract;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Auth;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
        // \App\Model::class => App\Policies\ModelPolicy::class,
    ];

    /**
     * Register any application authentication / authorization services.
     *
     * @return void
     */
    public function boot(GateContract $gate)
    {
        Auth::extend('edlib', function (Container $app) {
            return new EdlibGuard($app['request']);
        });

        parent::registerPolicies($gate);

        //
    }
}
