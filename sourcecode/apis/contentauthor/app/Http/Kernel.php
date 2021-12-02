<?php

namespace App\Http;

use App\Bootstrap\LoadDefaultEnvVariables;
use App\Http\Middleware\DraftAction;
use App\Http\Middleware\RequestId;
use App\Http\Middleware\AdapterMode;
use App\Http\Middleware\AddRequestId;
use App\Http\Middleware\APIAuth;
use App\Http\Middleware\GameAccess;
use App\Http\Middleware\QuestionSetAccess;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Foundation\Http\Kernel as HttpKernel;
use Illuminate\Routing\Router;

class Kernel extends HttpKernel
{
    /**
     * The application's global HTTP middleware stack.
     *
     * @var array
     */
    protected $middleware = [
        \Illuminate\Foundation\Http\Middleware\CheckForMaintenanceMode::class,
        \Illuminate\Foundation\Http\Middleware\ValidatePostSize::class,
        \App\Http\Middleware\TrimStrings::class,
        \Illuminate\Foundation\Http\Middleware\ConvertEmptyStringsToNull::class,
        \App\Http\Middleware\TrustProxies::class,
        RequestId::class
    ];

    /**
     * The application's route middleware groups.
     *
     * @var array
     */
    protected $middlewareGroups = [
        'web' => [
            \App\Http\Middleware\EncryptCookies::class,
            \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
            \Illuminate\Session\Middleware\StartSession::class,
            // \Illuminate\Session\Middleware\AuthenticateSession::class,
            \Illuminate\View\Middleware\ShareErrorsFromSession::class,
            \App\Http\Middleware\VerifyCsrfToken::class,
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
        ],
        'cerpusauth' => [
            \App\Http\Middleware\EncryptCookies::class,
            \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
            \Illuminate\Session\Middleware\StartSession::class,
            \Illuminate\View\Middleware\ShareErrorsFromSession::class,
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
        ],
        'api' => [
            'throttle:60,1',
            'bindings',
        ],
        'api-auth' => [
            \App\Http\Middleware\EncryptCookies::class,
            \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
            \Illuminate\Session\Middleware\StartSession::class,
            APIAuth::class,
            \Illuminate\View\Middleware\ShareErrorsFromSession::class,
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
            AddRequestId::class,
        ],
        'internal-api' => [
            'auth.internalApi',
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
        ]
    ];

    /**
     * The application's route middleware.
     *
     * @var array
     */
    protected $routeMiddleware = [
        'auth' => \App\Http\Middleware\Authenticate::class,
        'auth.basic' => \Illuminate\Auth\Middleware\AuthenticateWithBasicAuth::class,
        'auth.internalApi' => \App\Http\Middleware\InternalAuth::class,
        'bindings' => \Illuminate\Routing\Middleware\SubstituteBindings::class,
        'cache.headers' => \Illuminate\Http\Middleware\SetCacheHeaders::class,
        'can' => \Illuminate\Auth\Middleware\Authorize::class,
        'guest' => \App\Http\Middleware\RedirectIfAuthenticated::class,
        'signed' => \Illuminate\Routing\Middleware\ValidateSignature::class,
        'throttle' => \Illuminate\Routing\Middleware\ThrottleRequests::class,
        'verified' => \Illuminate\Auth\Middleware\EnsureEmailIsVerified::class,

        // Internal middleware
        'internal.handle-jwt' => \App\Http\Middleware\InternalHandleJwt::class,

        // App middleware
        'core.return' => \App\Http\Middleware\CoreReturnUrl::class,
        'core.auth' => \App\Http\Middleware\CerpusAuth::class,
        'core.ownership' => \App\Http\Middleware\CheckOwnership::class,
        'core.ltiauth' => \App\Http\Middleware\LtiRequestAuth::class,
        'core.locale' => \App\Http\Middleware\LtiLocale::class,
        'core.behavior-settings' => \App\Http\Middleware\LtiBehaviorSettings::class,
        'core.embed-url' => \App\Http\Middleware\LtiEmbedUrl::class,
        'signed.oauth10-request' => \App\Http\Middleware\SignedOauth10Request::class,
        'oauth' => \App\Http\Middleware\Oauth2Authentication::class,
        'lti.question-set' => \App\Http\Middleware\LtiQuestionSet::class,
        'lti.qs-to-request' => \App\Http\Middleware\AddExtQuestionSetToRequestMiddleware::class,
        'game-access' => GameAccess::class,
        'questionset-access' => QuestionSetAccess::class,
        'adaptermode' => AdapterMode::class,
        'draftaction' => DraftAction::class,
    ];

    /**
     * The priority-sorted list of middleware.
     *
     * This forces non-global middleware to always be in the given order.
     *
     * @var array
     */
    protected $middlewarePriority = [
        \Illuminate\Session\Middleware\StartSession::class,
        \Illuminate\View\Middleware\ShareErrorsFromSession::class,
        \App\Http\Middleware\Authenticate::class,
        \Illuminate\Session\Middleware\AuthenticateSession::class,
        \Illuminate\Routing\Middleware\SubstituteBindings::class,
        \Illuminate\Auth\Middleware\Authorize::class,
    ];

    public function __construct(Application $app, Router $router)
    {
        $this->bootstrappers = [
            LoadDefaultEnvVariables::class,
            ...$this->bootstrappers,
        ];

        parent::__construct($app, $router);
    }
}
