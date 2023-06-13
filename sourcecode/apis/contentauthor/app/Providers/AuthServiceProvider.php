<?php

namespace App\Providers;

use App\Administrator;
use App\Auth\Guards\EdlibGuard;
use Illuminate\Auth\GenericUser;
use Illuminate\Container\Container;
use Illuminate\Contracts\Auth\Access\Gate as GateContract;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

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

        Gate::define('superadmin', function (Administrator|GenericUser $user) {
            return (
                $user instanceof Administrator ||
                in_array('superadmin', $user->roles ?? [])
            );
        });
    }
}
