<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Events\LaunchItemSelection;

class AddContentInfoEndpointToLtiLaunch
{
    public function handle(LaunchItemSelection $event): void
    {
        $tool = $event->getTool();
        $event->setLaunch(
            $event
                ->getLaunch()
                ->withClaim('ext_edlib3_author_endpoint', url('/author/tool/' . $tool->id)),
        );
    }
}
