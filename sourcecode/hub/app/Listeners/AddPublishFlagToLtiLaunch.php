<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Events\LaunchItemSelection;

class AddPublishFlagToLtiLaunch
{
    public function handle(LaunchItemSelection $event): void
    {
        $version = $event->getContentVersion();

        if ($version === null) {
            return;
        }

        $event->setLaunch(
            $event->getLaunch()
                ->withClaim('ext_edlib3_published', $version->published ? '1' : '0'),
        );
    }
}
