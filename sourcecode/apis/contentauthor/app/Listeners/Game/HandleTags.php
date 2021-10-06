<?php

namespace App\Listeners\Game;

use App\Events\GameWasSaved;
use Cerpus\MetadataServiceClient\Exceptions\MetadataServiceException;

class HandleTags
{

    /**
     * @param GameWasSaved $event
     * @return $this|bool
     * @throws MetadataServiceException
     */
    public function handle(GameWasSaved $event)
    {
        /** @var \App\Game $game */
        $game = $event->game->fresh();
        $tags = $event->metadata->tags;
        if( !empty($tags) ){
            return $game->updateMetaTags($tags);
        }
        return true;
    }
}
