<?php

declare(strict_types=1);

namespace App\Events;

use App\Lti\LtiLaunchBuilder;
use App\Models\LtiTool;

final class LaunchLti
{
    public function __construct(
        private readonly string $url,
        private LtiLaunchBuilder $launch,
        private readonly LtiTool $tool,
    ) {}

    public function getUrl(): string
    {
        return $this->url;
    }

    public function getLaunch(): LtiLaunchBuilder
    {
        return $this->launch;
    }

    public function setLaunch(LtiLaunchBuilder $launch): void
    {
        $this->launch = $launch;
    }

    public function getTool(): LtiTool
    {
        return $this->tool;
    }
}
