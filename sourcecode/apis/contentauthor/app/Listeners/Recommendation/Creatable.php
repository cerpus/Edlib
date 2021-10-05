<?php


namespace App\Listeners\Recommendation;

trait Creatable
{
    /**
     * @param  ContentUpdated  $event
     * @return bool
     */
    protected function shouldExistInRecommendationEngine($event): bool
    {
        return $event->content->isUsableByEveryone()
            && !$event->content->hasPublicChildren();
    }
}
