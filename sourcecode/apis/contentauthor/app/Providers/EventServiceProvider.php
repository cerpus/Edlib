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
     *
     * @var array
     */
    protected $listen = [
        'App\Events\SomeEvent' => [
            'App\Listeners\EventListener',
        ],

        'App\Events\ArticleWasSaved' => [
            'App\Listeners\Article\HandleVersioning',
            'App\Listeners\Article\HandleCollaborators',
            'App\Listeners\Article\HandlePrivacy',
            'App\Listeners\Article\HandleCollaborationInviteEmails',
        ],

        'App\Events\ArticleWasCopied' => [
            'App\Listeners\Article\HandleVersioning',
        ],

        'App\Events\H5PWasCopied' => [
            'App\Listeners\H5P\Copy\HandleVersioning',
        ],

        'App\Events\H5PWasSaved' => [
            'App\Listeners\H5P\HandleVersioning',
            HandleExport::class,
        ],

        'App\Events\LinkWasSaved' => [
            'App\Listeners\Link\HandleVersioning',
        ],

        'App\Events\VideoSourceChanged' => [
            'App\Listeners\H5P\HandleVideoSource',
        ],

        'App\Events\QuestionsetWasSaved' => [
            'App\Listeners\Questionset\HandlePrivacy',
            'App\Listeners\Questionset\HandleQuestionbank',
        ],

        'App\Events\GameWasSaved' => [
            'App\Listeners\Game\HandlePrivacy',
            'App\Listeners\Game\HandleVersioning',
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
//            UpdateContentInRecommendationEngine::class,
        ],

        ContentDeleting::class => [
            //
        ],

        ContentDeleted::class => [
//            RemoveContentFromRecommendationEngine::class,
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
