<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Events\LaunchItemSelection;

class AddContentStatusFlagsToLtiLaunch
{
    public function handle(LaunchItemSelection $event): void
    {
        $version = $event->getContentVersion();

        if ($version === null) {
            return;
        }

        $content = $version->content;
        assert($content !== null);

        $event->setLaunch(
            $event->getLaunch()
                ->withClaim('ext_edlib3_published', $version->published ? '1' : '0')
                ->withClaim('ext_edlib3_shared', $content->shared ? '1' : '0'),
        );
    }
}
