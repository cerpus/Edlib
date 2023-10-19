<?php

declare(strict_types=1);

namespace App\Providers;

use App\Models\Content;
use App\Models\LtiTool;
use App\Models\User;
use App\Policies\ContentPolicy;
use App\Policies\LtiToolPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        Content::class => ContentPolicy::class,
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
    }
}
