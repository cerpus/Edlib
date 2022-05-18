<?php

namespace App\Events;

use App\Game;
use App\Libraries\DataObjects\ResourceMetadataDataObject;
use Illuminate\Queue\SerializesModels;

class GameWasSaved extends Event
{
    use SerializesModels;

    public $game;

    public function __construct(
        Game $game,
        public readonly ResourceMetadataDataObject $metadata,
    ) {
        $this->game = $game;
    }
}
