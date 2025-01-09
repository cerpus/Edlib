<?php

declare(strict_types=1);

namespace App\Events;

use App\Models\LtiPlatform;

final readonly class LtiPlatformDeleting
{
    public function __construct(
        public LtiPlatform $ltiPlatform,
    ) {
    }
}
