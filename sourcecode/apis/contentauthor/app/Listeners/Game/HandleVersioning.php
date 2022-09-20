<?php

namespace App\Listeners\Game;

use App\Events\GameWasSaved;
use App\Libraries\Versioning\VersionableObject;
use App\Listeners\AbstractHandleVersioning;
use Cerpus\VersionClient\VersionClient;

class HandleVersioning extends AbstractHandleVersioning
{
    protected $versionClient;

    protected $game;

    public function __construct(VersionClient $versionClient)
    {
        $this->versionClient = $versionClient;
    }


    public function handle(GameWasSaved $event)
    {
        $this->game = $event->game->fresh();
        $reason = $event->metadata->reason;

        $this->handleSave($this->game, $reason);
    }

    public function getParentVersionId()
    {
        return $this->game->version_id;
    }

    protected function getExternalUrl(VersionableObject $object)
    {
        return route('game.show', $this->game->id);
    }
}
