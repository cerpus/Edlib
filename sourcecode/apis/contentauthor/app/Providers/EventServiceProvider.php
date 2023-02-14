<?php

namespace App\Providers;

use App\Events\ContentCreated;
use App\Events\ContentCreating;
use App\Events\ContentDeleted;
use App\Events\ContentDeleting;
use App\Events\ContentUpdated;
use App\Events\ContentUpdating;
use App\Events\ResourceSaved;
use App\Listeners\H5P\HandleExport;
use App\Listeners\ResourceEventHandler;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     */
    protected $listen = [
        \App\Events\SomeEvent::class => [
            \App\Listeners\EventListener::class,
        ],

        \App\Events\ArticleWasSaved::class => [
            \App\Listeners\Article\HandleVersioning::class,
            \App\Listeners\Article\HandleCollaborators::class,
            \App\Listeners\Article\HandlePrivacy::class,
            \App\Listeners\Article\HandleCollaborationInviteEmails::class,
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

        \App\Events\LinkWasSaved::class => [
            \App\Listeners\Link\HandleVersioning::class,
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

        ResourceSaved::class => [
            ResourceEventHandler::class,
        ],

        ContentCreating::class => [
            //
        ],

        ContentCreated::class => [
//            CreateContentInRecommendationEngine::class,
        ],

        ContentUpdating::class => [
            //
        ],

        ContentUpdated::class => [
        ],

        ContentDeleting::class => [
            //
        ],

        ContentDeleted::class => [
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
