<?php

declare(strict_types=1);

namespace App\Providers;

use App\Configuration\Features;
use App\Configuration\NdlaLegacyConfig;
use App\Models\Content;
use App\Models\LtiPlatform;
use App\Models\LtiTool;
use App\Models\User;
use App\Policies\ContentPolicy;
use App\Policies\LtiPlatformPolicy;
use App\Policies\LtiToolPolicy;
use App\Support\Jwt\JwtDecoderInterface;
use App\Support\Jwt\JwtException;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

use function is_string;
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
        Auth::viaRequest('ndla-legacy', function (Request $request) {
            $bearerToken = $request->bearerToken();
            if (!is_string($bearerToken)) {
                abort(401, 'Missing bearer token');
            }

            $jwtDecoder = $this->app->make(JwtDecoderInterface::class);
            $config = $this->app->make(NdlaLegacyConfig::class);
            $key = $config->getPublicKeyOrJwksUri();

            try {
                $payload = $jwtDecoder->getVerifiedPayload($bearerToken, $key);
            } catch (JwtException) {
                abort(401, 'Invalid JWT');
            }

            $id = $payload->{'https://ndla.no/ndla_id'} ?? null;
            $name = $payload->{'https://ndla.no/user_name'} ?? null;
            $email = $payload->{'https://ndla.no/user_email'} ?? null;

            if (!is_string($id) || !is_string($email)) {
                abort(400, 'Missing ID and/or email');
            }

            return User::where('ndla_id', $id)->firstOr(function () use ($id, $name, $email) {
                $user = User::firstOrNew(
                    ['email' => $email],
                    ['name' => $name ?? $email],
                );
                $user->ndla_id = $id;
                $user->save();

                return $user;
            });
        });

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

        Gate::define('update-account', function (User $user) {
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
