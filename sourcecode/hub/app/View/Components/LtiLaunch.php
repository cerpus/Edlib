<?php

namespace App\View\Components;

use App\Lti\Oauth1Request;
use App\Lti\Oauth1SignerFactory;
use App\Models\LtiTool;
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

    public Oauth1Request $oauth1Request;

    public function __construct(
        Oauth1SignerFactory $signerFactory,
        public readonly LtiTool $ltiTool,
        public readonly string $launchUrl,
        public readonly bool $preview = false,
        public readonly string $locale = 'en-US',
        public readonly int $width = 640,
        public readonly int $height = 480,
    ) {
        $this->uniqueId = (string) Uuid::v4();

        $this->oauth1Request = $signerFactory
            ->create($ltiTool->getOauth1Credentials())
            ->sign('POST', $launchUrl, [
                'lti_message_type' => 'basic-lti-launch-request',
                'lti_version' => 'LTI-1p0',
                'ext_preview' => $preview ? '1' : '',
                'launch_presentation_width' => (string) $width,
                'launch_presentation_height' => (string) $height,
            ]);
    }

    public function render(): View
    {
        return match ($this->ltiTool->lti_version) {
            LtiVersion::Lti1_1 => view('components.lti11-launch'),
            LtiVersion::Lti1_3 => view('components.lti13-launch'),
        };
    }
}
