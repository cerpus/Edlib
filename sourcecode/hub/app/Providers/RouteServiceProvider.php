<?php

declare(strict_types=1);

namespace App\Providers;

use App\Configuration\NdlaLegacyConfig;
use App\Http\Controllers\HealthController;
use App\Models\Content;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;
use Laravel\Nightwatch\Http\Middleware\Sample;
use RuntimeException;

use function base_path;
use function config;
use function is_string;
use function parse_url;

use const PHP_URL_HOST;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * The path to the "home" route for your application.
     *
     * Typically, users are redirected here after authentication.
     *
     * @var string
     */
    public const HOME = '/home';

    /**
     * Define your route model bindings, pattern filters, and other route configuration.
     */
    public function boot(): void
    {
        Route::bind('apiContent', function (string $value) {
            return Content::withoutGlobalScope('atLeastOneVersion')
                ->withTrashed()
                ->where('id', $value)
                ->firstOrFail();
        });

        Route::bind('edlib2UsageContent', fn(string $value) => Content::firstWithEdlib2UsageIdOrFail($value));

        $this->configureRateLimiting();

        $this->routes(function (NdlaLegacyConfig $ndlaLegacy) {
            $domain = parse_url(config('app.url'), PHP_URL_HOST);
            if (!is_string($domain)) {
                throw new RuntimeException('APP_URL must be set and valid');
            }

            // must exist on all domains
            Route::middleware('stateless')
                ->middleware(Sample::never())
                ->get('/up', HealthController::class);

            Route::middleware('stateless')
                ->group(base_path('routes/edlib-legacy.php'));

            Route::middleware('ndla-legacy')
                ->domain($ndlaLegacy->isEnabled() ? $ndlaLegacy->getDomain() : 'invalid.')
                ->group(base_path('routes/ndla-legacy.php'));

            Route::middleware('api')
                ->domain($domain)
                ->prefix('api')
                ->group(base_path('routes/api.php'));

            Route::middleware('stateless')
                ->domain($domain)
                ->group(base_path('routes/stateless.php'));

            Route::middleware('web')
                ->domain($domain)
                ->group(base_path('routes/web.php'));
        });
    }

    /**
     * Configure the rate limiters for the application.
     */
    protected function configureRateLimiting(): void
    {
        RateLimiter::for('api', function (Request $request) {
            return Limit::none();
        });
    }
}
