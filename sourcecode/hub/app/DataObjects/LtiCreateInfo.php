<?php

declare(strict_types=1);

namespace App\DataObjects;

use App\Models\LtiTool;
use App\Models\LtiToolExtra;

class LtiCreateInfo
{
    public function __construct(
        public string $name,
        public string $url,
        public string|null $imageUrl,
    ) {}

    public static function fromLtiTool(LtiTool $tool): self
    {
        return new self(
            $tool->name,
            route('content.launch-creator', [$tool]),
            self::getImageUrl($tool->name, $tool->creator_launch_url),
        );
    }

    public static function fromLtiToolExtra(LtiTool $tool, LtiToolExtra $ltiToolExtra): self
    {
        return new self(
            $ltiToolExtra->name,
            route('content.launch-creator', [$tool, $ltiToolExtra]),
            self::getImageUrl($ltiToolExtra->name, $ltiToolExtra->lti_launch_url),
        );
    }

    public static function getImageUrl(string $name, string $url): string|null
    {
        if (str_contains(strtolower($name), 'content author')) {
            return asset('images/lti-tools/h5p.jpg');
        }

        if (str_contains(strtolower($name), 'h5p')) {
            return asset('images/lti-tools/h5p.jpg');
        }

        if (str_contains(strtolower($name), 'millionaire')) {
            return asset('images/lti-tools/millionaire.png');
        }

        return null;
    }
}
