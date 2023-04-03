<?php

declare(strict_types=1);

namespace Tests\Browser\Components;

use Laravel\Dusk\Browser;
use Laravel\Dusk\Component;

class ContentCard extends Component
{
    public function selector(): string
    {
        return '.content-card';
    }

    public function assert(Browser $browser): void
    {
        $browser->assertVisible($this->selector());
    }

    public static function siteElements(): array
    {
        return [];
    }
}
