<?php

declare(strict_types=1);

namespace Tests\Browser\Components;

use Laravel\Dusk\Browser;
use Laravel\Dusk\Component;

class VersionHistory extends Component
{
    public function selector(): string
    {
        return '.version-history';
    }

    public function assert(Browser $browser): void
    {
        $browser->assertVisible($this->selector());
    }

    /**
     * @return array<string, string>
     */
    public function elements(): array
    {
        return [
            '@version' => '> tbody > tr',
        ];
    }
}
