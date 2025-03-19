<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Events\LaunchItemSelection;

use function assert;

class AddContentStatusFlagsToLtiLaunch
{
    public function handle(LaunchItemSelection $event): void
    {
        $version = $event->getContentVersion();

        if ($version !== null) {
            $content = $version->content;
            assert($content !== null);

            $published = $version->published;
            $shared = $content->shared;
        } else {
            $published = $event->getTool()->default_published;
            $shared = $event->getTool()->default_shared;
        }

        $event->setLaunch(
            $event->getLaunch()
                ->withClaim('ext_edlib3_published', $published ? '1' : '0')
                ->withClaim('ext_edlib3_shared', $shared ? '1' : '0'),
        );
    }
}
