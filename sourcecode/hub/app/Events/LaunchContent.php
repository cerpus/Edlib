<?php

declare(strict_types=1);

namespace App\Events;

use App\Lti\LtiLaunchBuilder;
use App\Models\ContentVersion;

final class LaunchContent
{
    public function __construct(
        private readonly string $url,
        private readonly ContentVersion $contentVersion,
        private LtiLaunchBuilder $launch,
    ) {}

    public function getUrl(): string
    {
        return $this->url;
    }

    public function getContentVersion(): ContentVersion
    {
        return $this->contentVersion;
    }

    public function getLaunch(): LtiLaunchBuilder
    {
        return $this->launch;
    }

    public function setLaunch(LtiLaunchBuilder $launch): void
    {
        $this->launch = $launch;
    }
}
