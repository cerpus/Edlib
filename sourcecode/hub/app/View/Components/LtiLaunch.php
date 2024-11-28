<?php

declare(strict_types=1);

namespace App\View\Components;

use Illuminate\Contracts\Encryption\Encrypter;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use Illuminate\View\Component;

use function view;

class LtiLaunch extends Component
{
    public readonly string $url;

    public readonly string $uniqueId;

    /**
     * @param string $logTo
     *     A selector for an element in which to log messages sent by the
     *     iframe.
     * @param array<string, string> $parameters
     */
    public function __construct(
        private Encrypter $encrypter,
        public \App\Lti\LtiLaunch $launch,
        public string $method = 'GET',
        public array $parameters = [],
        public string $target = '_self',
        public int $width = 640,
        public int $height = 480,
        public string $logTo = '',
        public bool $forwardsResizeMessages = false,
        public bool $direct = false,
    ) {
        $this->url = URL::signedRoute('lti.launch', [
            'launch' => $this->encrypter->encrypt($launch),
        ]);

        $this->uniqueId = Str::uuid()->toString();
    }

    public function render(): View
    {
        return view('components.launch');
    }
}
