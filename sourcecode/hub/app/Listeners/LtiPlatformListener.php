<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Events\LtiPlatformDeleting;

class LtiPlatformListener
{
    public function handleDeleting(LtiPlatformDeleting $event): void
    {
        $event->ltiPlatform->contexts()->detach();
    }
}
