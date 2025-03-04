<?php

declare(strict_types=1);

namespace App\Events;

use App\Lti\LtiLaunchBuilder;
use App\Models\ContentVersion;

class LaunchItemSelection
{
    public function __construct(
        private LtiLaunchBuilder $launch,
        private readonly ContentVersion|null $contentVersion,
    ) {}

    public function getLaunch(): LtiLaunchBuilder
    {
        return $this->launch;
    }

    public function setLaunch(LtiLaunchBuilder $launch): void
    {
        $this->launch = $launch;
    }

    public function getContentVersion(): ContentVersion|null
    {
        return $this->contentVersion;
    }
}
