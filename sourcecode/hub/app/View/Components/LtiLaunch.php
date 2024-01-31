<?php

declare(strict_types=1);

namespace App\View\Components;

use Illuminate\Contracts\Encryption\Encrypter;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\URL;
use Illuminate\View\Component;

use function view;

class LtiLaunch extends Component
{
    public readonly string $url;

    /**
     * @param string $logTo
     *     A selector for an element in which to log messages sent by the
     *     iframe.
     */
    public function __construct(
        private Encrypter $encrypter,
        public \App\Lti\LtiLaunch $launch,
        public string $logTo = '',
    ) {
        $this->url = URL::signedRoute('lti.launch', [
            'launch' => $this->encrypter->encrypt($launch),
        ]);
    }

    public function render(): View
    {
        return view('components.launch');
    }
}
