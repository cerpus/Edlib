<?php

namespace App\Providers;

use App\Listeners\H5P\HandleExport;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     */
    protected $listen = [
        // TODO: enable auto-discovery

        \App\Events\H5pContentUpdated::class => [
            \App\Listeners\H5P\InfoCacheListener::class . '@handleUpdated',
        ],

        \App\Events\H5pContentDeleted::class => [
            \App\Listeners\H5P\InfoCacheListener::class . '@handleDeleted',
        ],

        \App\Events\H5PWasSaved::class => [
            HandleExport::class,
        ],

        \App\Events\VideoSourceChanged::class => [
            \App\Listeners\H5P\HandleVideoSource::class,
        ],

        \App\Events\QuestionsetWasSaved::class => [
            \App\Listeners\Questionset\HandleQuestionbank::class,
        ],
    ];

    /**
     * Register any other events for your application.
     *
     * @return void
     */
    public function boot()
    {
        parent::boot();
    }
}
