<?php

namespace App\Listeners\Game;

use App\Events\GameWasSaved;

class HandlePrivacy
{
    public function handle(GameWasSaved $event)
    {
        /** @var \App\Game $game */
        $game = $event->game->fresh();
        $game->is_private = strtoupper($event->metadata->share ?? 'PRIVATE') === 'PRIVATE';

        return $game->save();
    }
}
