<?php

declare(strict_types=1);

namespace Tests\Browser\Components;

use Laravel\Dusk\Browser;
use Laravel\Dusk\Component;
use Override;

class PreviewModal extends Component
{
    #[Override] public function selector(): string
    {
        return '.preview-modal';
    }

    #[Override] public function assert(Browser $browser): void
    {
        $browser->assertVisible($this->selector());
    }

    /**
     * @return array<string, string>
     */
    #[Override] public function elements(): array
    {
        return [
            '@preview' => '.lti-launch',
            '@use-button' => '.use-button',
        ];
    }
}
