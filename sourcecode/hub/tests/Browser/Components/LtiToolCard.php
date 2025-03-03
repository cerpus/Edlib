<?php

declare(strict_types=1);

namespace Tests\Browser\Components;

use Laravel\Dusk\Browser;
use Laravel\Dusk\Component;

final class LtiToolCard extends Component
{
    public function selector(): string
    {
        return '.lti-tool-card';
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
            '@send-email' => '.lti-tool-card-send-email',
            '@send-name' => '.lti-tool-card-send-name',
        ];
    }
}
