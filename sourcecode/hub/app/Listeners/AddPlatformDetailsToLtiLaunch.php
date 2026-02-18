<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Events\LaunchLti;

final readonly class AddPlatformDetailsToLtiLaunch
{
    public function handleLaunch(LaunchLti $event): void
    {
        $event->setLaunch(
            $event->getLaunch()
                ->withClaim('tool_consumer_info_product_family_code', 'Edlib')
                ->withClaim('tool_consumer_info_version', '3')
                ->withClaim('tool_consumer_instance_name', config('app.name'))
                ->withClaim('tool_consumer_instance_url', config('app.url')),
        );
    }
}
