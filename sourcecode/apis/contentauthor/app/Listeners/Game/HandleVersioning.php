<?php

namespace App\Listeners\Game;

use App\Events\GameWasSaved;
use App\Listeners\AbstractHandleVersioning;

class HandleVersioning extends AbstractHandleVersioning
{
    protected $game;

    public function handle(GameWasSaved $event)
    {
        $this->game = $event->game->fresh();

        $this->handleSave($this->game, $event->metadata->reason);
    }

    public function getParentVersionId()
    {
        return $this->game->version_id;
    }
}
