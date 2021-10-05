<?php

namespace App\Listeners\Game;

use App\ACL\ArticleAccess;
use App\Events\GameWasSaved;
use App\Http\Libraries\License;

class HandleLicensing
{
    use ArticleAccess;

    protected $license;

    public function handle(GameWasSaved $event)
    {
        try {
            $game = $event->game->fresh();
            $metadata = $event->metadata;

            $this->license = app()->make(License::class);
            $licenseContent = $this->license->getOrAddContent($game);
            if ($licenseContent) {
                $this->license->setLicense($metadata->get('license', 'BY'), $game->id);
            }
        } catch (Exception $e) {
            Log::error(__METHOD__ . ': Unable to add License to game ' . $game->id . '. ' . $e->getCode() . ': ' . $e->getMessage());
        }
    }
}
