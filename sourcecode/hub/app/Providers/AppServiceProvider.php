<?php

declare(strict_types=1);

namespace App\Providers;

use App\Configuration\Locales;
use App\Lti\Serializer\ContentItemsSerializer;
use App\Lti\Serializer\LtiContentSerializer;
use App\Support\CarbonToPsrClockAdapter;
use App\Support\SessionScopeAwareRouteUrlGenerator;
use Cerpus\EdlibResourceKit\Lti\Lti11\Serializer\DeepLinking\ContentItemsSerializerInterface;
use Cerpus\EdlibResourceKit\Lti\Lti11\Serializer\DeepLinking\LtiLinkItemSerializerInterface;
use Illuminate\Pagination\Paginator;
use Illuminate\Routing\UrlGenerator;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;
use Laravel\Scout\Console\DeleteIndexCommand;
use Laravel\Scout\Console\ImportCommand;
use Laravel\Scout\Console\SyncIndexSettingsCommand;
use Laravel\Telescope\Telescope;
use Psr\Clock\ClockInterface;
use Random\Randomizer;

use function class_exists;

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

        $this->app->extend(
            ContentItemsSerializerInterface::class,
            fn (ContentItemsSerializerInterface $serializer) => new ContentItemsSerializer($serializer),
        );
        $this->app->extend(
            LtiLinkItemSerializerInterface::class,
            fn (LtiLinkItemSerializerInterface $serializer) => new LtiContentSerializer($serializer),
        );

        // FIXME: get rid of this horror show when Laravel allows decorating the
        // UrlGenerator service.
        $this->app->extend('url', function (UrlGenerator $urlGenerator): UrlGenerator {
            (function () use ($urlGenerator): void {
                /** @var UrlGenerator $this */
                $this->routeGenerator = new SessionScopeAwareRouteUrlGenerator($urlGenerator, $this->request);
            })->call($urlGenerator);

            return $urlGenerator;
        });
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
