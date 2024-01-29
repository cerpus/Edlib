<?php

declare(strict_types=1);

namespace App\View\Components;

use Illuminate\Contracts\Encryption\Encrypter;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\URL;
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

    public readonly string $iframeUrl;

    public function __construct(
        private Encrypter $encrypter,
        public \App\Lti\LtiLaunch $launch,
        public string $logTo = '',
    ) {
        $this->uniqueId = (string) Uuid::v4();

        $this->iframeUrl = URL::signedRoute('lti.launch', [
            'launch' => $this->encrypter->encrypt($this->launch),
        ]);
    }

    public function render(): View
    {
        return view('components.lti-launch');
    }
}
