<?php

declare(strict_types=1);

namespace App\Enums;

enum ContentViewSource: string
{
    case Detail = 'detail';
    case Embed = 'embed';
    case LtiPlatform = 'lti_platform';
    case Share = 'standalone';

    public function isDetail(): bool
    {
        return $this === self::Detail;
    }

    public function isEmbed(): bool
    {
        return $this === self::Embed;
    }

    public function isLtiPlatform(): bool
    {
        return $this === self::LtiPlatform;
    }

    public function isShare(): bool
    {
        return $this === self::Share;
    }
}
