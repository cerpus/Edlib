<?php

namespace App\Listeners\Recommendation;

use App\Events\ContentCreated;
use App\Jobs\REUpdateOrCreate;
use Illuminate\Contracts\Queue\ShouldQueue;

class CreateContentInRecommendationEngine implements ShouldQueue
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
     * @param  ContentCreated  $event
     * @return void
     */
    public function handle(ContentCreated $event)
    {
        REUpdateOrCreate::dispatch($event->content->refresh())
            ->onQueue("re-content");
    }
}
