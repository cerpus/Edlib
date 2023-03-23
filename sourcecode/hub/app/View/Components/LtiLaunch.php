<?php

namespace App\View\Components;

use App\Models\LtiVersion;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;
use Symfony\Component\Uid\Uuid;
use function view;

class LtiLaunch extends Component
{
    /**
     * Uniquely identify the iframe/form pair when multiple are rendered on the
     * same page.
     */
    public readonly string $uniqueId;

    public function __construct(
        public readonly string $launchUrl,
        public readonly LtiVersion $ltiVersion,
        public readonly bool $preview = false,
        public readonly string $locale = 'en-US',
        public readonly int $width = 640,
        public readonly int $height = 480,
    ) {
        $this->uniqueId = (string) Uuid::v4();
    }

    public function render(): View
    {
        return match ($this->ltiVersion) {
            LtiVersion::Lti1_1 => view('components.lti11-launch'),
            LtiVersion::Lti1_3 => view('components.lti13-launch'),
        };
    }
}
