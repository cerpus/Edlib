<?php

declare(strict_types=1);

namespace App\Providers;

use App\Configuration\Features;
use App\Models\Content;
use App\Models\LtiPlatform;
use App\Models\LtiTool;
use App\Models\User;
use App\Policies\ContentPolicy;
use App\Policies\LtiPlatformPolicy;
use App\Policies\LtiToolPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

use function request;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        Content::class => ContentPolicy::class,
        LtiPlatform::class => LtiPlatformPolicy::class,
        LtiTool::class => LtiToolPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        Gate::define('admin', function (User $user) {
            return $user->admin ?? false;
        });

        Gate::define('login', function (User|null $user) {
            $request = request();

            return !$request->hasPreviousSession() || !$request->session()->has('lti');
        });

        Gate::define('logout', function (User $user) {
            return !request()->session()->has('lti');
        });

        Gate::define('register', function (User|null $user) {
            $features = app()->make(Features::class);

            if (!$features->isSignupEnabled()) {
                return false;
            }

            $request = request();

            return !$request->hasPreviousSession() ||
                !$request->session()->has('lti');
        });

        Gate::define('reset-password', function (User|null $user) {
            $features = app()->make(Features::class);

            return $features->isForgotPasswordEnabled();
        });
    }
}
