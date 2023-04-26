<?php

namespace App\Providers;

use App\Configuration\Locales;
use App\Support\CarbonToPsrClockAdapter;
use Barryvdh\LaravelIdeHelper\IdeHelperServiceProvider;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\ServiceProvider;
use Laravel\Scout\Console\DeleteIndexCommand;
use Laravel\Scout\Console\ImportCommand;
use Laravel\Scout\Console\SyncIndexSettingsCommand;
use Psr\Clock\ClockInterface;
use Random\Randomizer;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        if ($this->app->isLocal()) {
            $this->app->register(IdeHelperServiceProvider::class);

            $this->app->register(\Laravel\Telescope\TelescopeServiceProvider::class);
            $this->app->register(TelescopeServiceProvider::class);
        }

        $this->app->bind(ClockInterface::class, CarbonToPsrClockAdapter::class);
        $this->app->bind(Randomizer::class);

        $this->app->when(Locales::class)
            ->needs('$locales')
            ->giveConfig('app.allowed_locales');
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
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
