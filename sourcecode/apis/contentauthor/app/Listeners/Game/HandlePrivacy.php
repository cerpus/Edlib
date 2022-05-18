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
        $game->is_private = strtoupper($event->metadata->share ?? 'PRIVATE') === 'PRIVATE';

        return $game->save();
    }
}
