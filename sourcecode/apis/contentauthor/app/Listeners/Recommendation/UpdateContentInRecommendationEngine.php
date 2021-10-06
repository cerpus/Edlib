<?php

namespace App\Listeners\Recommendation;

use App\Events\ContentUpdated;
use App\Jobs\REUpdateOrCreate;

class UpdateContentInRecommendationEngine
{
    use Creatable;

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
     * @param  ContentUpdated  $event
     * @return void
     */
    public function handle(ContentUpdated $event)
    {
        REUpdateOrCreate::dispatch($event->content->refresh())
            ->onQueue("re-content");
    }
}
