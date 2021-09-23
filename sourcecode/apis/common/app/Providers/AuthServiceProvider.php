<?php

namespace App\Providers;

use App\Auth\IdentityServiceAuthenticator;
use App\User;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Http\Request;
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
        // 'App\Models\Model' => 'App\Policies\ModelPolicy',
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();

        Gate::define('admin', fn(User $user) => $user->isAdmin());

        Auth::viaRequest('jwt', function (Request $request) {
            $authenticator = $this->app->make(IdentityServiceAuthenticator::class);

            return $authenticator($request);
        });
    }
}
