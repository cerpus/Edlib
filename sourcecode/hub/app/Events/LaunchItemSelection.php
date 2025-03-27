<?php

declare(strict_types=1);

namespace App\Events;

use App\Lti\LtiLaunchBuilder;
use App\Models\ContentVersion;
use App\Models\LtiTool;

class LaunchItemSelection
{
    public function __construct(
        private LtiLaunchBuilder $launch,
        private readonly ContentVersion|null $contentVersion,
        private readonly LtiTool $tool,
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

    public function getTool(): LtiTool
    {
        return $this->tool;
    }
}
