<?php

declare(strict_types=1);

namespace Tests\Browser\Components;

use Laravel\Dusk\Browser;
use Laravel\Dusk\Component;

class LtiPlatformCard extends Component
{
    public function selector(): string
    {
        return '.lti-platform-card';
    }

    public function assert(Browser $browser): void
    {
        $browser->assertVisible($this->selector());
    }

    /**
     * @return array<mixed>
     */
    public function elements(): array
    {
        return [
            '@title' => '.lti-platform-card-title',
            '@authorizes-edit' => '.lti-platform-card-authorizes-edit',
            '@enable-sso' => '.lti-platform-card-enable-sso',
            '@context-count' => '.lti-platform-card-context-count',
        ];
    }
}
