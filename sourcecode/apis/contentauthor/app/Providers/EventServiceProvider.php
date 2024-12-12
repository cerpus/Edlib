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
        \App\Events\ArticleWasSaved::class => [
            \App\Listeners\Article\HandleVersioning::class,
            \App\Listeners\Article\HandleCollaborators::class,
            \App\Listeners\Article\HandlePrivacy::class,
        ],

        \App\Events\ArticleWasCopied::class => [
            \App\Listeners\Article\HandleVersioning::class,
        ],

        \App\Events\H5PWasCopied::class => [
            \App\Listeners\H5P\Copy\HandleVersioning::class,
        ],

        \App\Events\H5PWasSaved::class => [
            \App\Listeners\H5P\HandleVersioning::class,
            HandleExport::class,
        ],

        \App\Events\VideoSourceChanged::class => [
            \App\Listeners\H5P\HandleVideoSource::class,
        ],

        \App\Events\QuestionsetWasSaved::class => [
            \App\Listeners\Questionset\HandlePrivacy::class,
            \App\Listeners\Questionset\HandleQuestionbank::class,
        ],

        \App\Events\GameWasSaved::class => [
            \App\Listeners\Game\HandlePrivacy::class,
            \App\Listeners\Game\HandleVersioning::class,
//            'App\Listeners\ResourceEventSubscriber@onGameSaved', //TODO Comment in when H5P also has 'on...Saved' logic
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
