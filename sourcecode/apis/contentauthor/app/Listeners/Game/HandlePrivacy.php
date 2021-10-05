<?php

namespace App\Listeners\Game;

use App\ACL\ArticleAccess;
use App\Events\GameWasSaved;

class HandlePrivacy
{
    use ArticleAccess;

    public function handle(GameWasSaved $event)
    {
        /** @var \App\Game $game */
        $game = $event->game->fresh();
        $metadata = $event->metadata;
        $isPrivate = (mb_strtoupper($metadata->get('share', "PRIVATE")) === 'PRIVATE');
        $game->is_private = $isPrivate;
        return $game->save();
    }
}
