<?php

namespace App\Events;

use App\Game;
use App\Libraries\DataObjects\ResourceMetadataDataObject;
use Illuminate\Queue\SerializesModels;

class GameWasSaved extends Event
{
    use SerializesModels;

    public $game, $metadata;

    public function __construct(Game $game, ResourceMetadataDataObject $metadata)
    {
        $this->game = $game;
        $this->metadata = $metadata;
    }
}
