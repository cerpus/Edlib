<?php

declare(strict_types=1);

namespace Tests\Browser\Components;

use Laravel\Dusk\Browser;
use Laravel\Dusk\Component;

class LtiPlatformAddedAlert extends Component
{
    public function selector(): string
    {
        return '.lti-platform-added-alert';
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
            '@key' => '.lti-platform-added-alert-key',
            '@secret' => '.lti-platform-added-alert-secret',
        ];
    }
}
