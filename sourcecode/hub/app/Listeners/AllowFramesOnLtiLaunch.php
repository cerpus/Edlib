<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Events\LaunchLti;
use App\Http\Middleware\ContentSecurityPolicy;
use Illuminate\Http\Request;

/**
 * Make an exception for frames in CSP upon building an LTI launch.
 */
final readonly class AllowFramesOnLtiLaunch
{
    public function __construct(private Request $request)
    {
    }

    public function handleLaunch(LaunchLti $event): void
    {
        ContentSecurityPolicy::allowFrame($this->request, $event->getUrl());

        $returnUrl = $event->getLaunch()->getClaim('content_item_return_url');

        if ($returnUrl !== null) {
            ContentSecurityPolicy::allowFrame($this->request, $returnUrl);
        }
    }
}
