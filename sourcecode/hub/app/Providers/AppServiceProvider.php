<?php

declare(strict_types=1);

namespace App\Providers;

use App\Configuration\Locales;
use App\Policies\ContentPolicy;
use App\Support\CarbonToPsrClockAdapter;
use App\Support\SessionScope;
use App\Support\SessionScopeAwareRouteUrlGenerator;
use App\Utils\IconDownloader;
use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use Illuminate\Contracts\Filesystem\Cloud;
use Illuminate\Pagination\Paginator;
use Illuminate\Routing\UrlGenerator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;
use Laravel\Scout\Console\DeleteIndexCommand;
use Laravel\Scout\Console\ImportCommand;
use Laravel\Scout\Console\SyncIndexSettingsCommand;
use Laravel\Telescope\Telescope;
use Psr\Clock\ClockInterface;
use Random\Randomizer;

use function class_exists;
use function config;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        if ($this->app->isLocal() && class_exists(Telescope::class)) {
            $this->app->register(TelescopeServiceProvider::class);
        }

        $this->app->bind(ClockInterface::class, CarbonToPsrClockAdapter::class);
        $this->app->bind(Randomizer::class);

        $this->app->when(Locales::class)
            ->needs('$locales')
            ->giveConfig('app.allowed_locales');

        // FIXME: get rid of this horror show when Laravel allows decorating the
        // UrlGenerator service.
        $this->app->extend('url', function (UrlGenerator $urlGenerator): UrlGenerator {
            (function () use ($urlGenerator): void {
                /** @var UrlGenerator $this */
                $this->routeGenerator = new SessionScopeAwareRouteUrlGenerator($urlGenerator, $this->request);
            })->call($urlGenerator);

            return $urlGenerator;
        });

        $this->app->when(IconDownloader::class)
            ->needs(Cloud::class)
            ->give(fn () => Storage::disk('uploads'));

        $this->app->when(IconDownloader::class)
            ->needs(ClientInterface::class)
            ->give(fn () => new Client([
                'headers' => [
                    'User-Agent' => sprintf(
                        'Edlib/3 (+%s)',
                        config('app.contact-url') ?: config('app.url'),
                    ),
                ],
            ]));

        $this->app->singleton(ContentPolicy::class);
        $this->app->singleton(SessionScope::class);
        $this->app->singleton(ClockInterface::class, CarbonToPsrClockAdapter::class);
        $this->app->singleton(Randomizer::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Vite::useCspNonce();
        Vite::useStyleTagAttributes(['crossorigin' => 'anonymous']);

        Paginator::useBootstrapFive();

        // Scout doesn't register these in non-CLI environments, but we need
        // them to be accessible from queued jobs.
        $this->commands([
            DeleteIndexCommand::class,
            SyncIndexSettingsCommand::class,
            ImportCommand::class,
        ]);
    }
}
