<?php

namespace App\Listeners\Recommendation;

use App\Events\ContentDeleted;
use Cerpus\REContentClient\ContentClient;
use Cerpus\REContentClient\REContent;

class RemoveContentFromRecommendationEngine
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  ContentDeleted  $event
     * @return void
     */
    public function handle(ContentDeleted $event)
    {
        /** @var ContentClient $reCClient */
        $reCClient = app(ContentClient::class);

        $reContent = app(REContent::class);
        $reContent->setId($event->content->id);
        $reCClient->removeIfExists($reContent);
    }
}
