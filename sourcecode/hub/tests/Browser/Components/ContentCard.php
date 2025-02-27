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

    /**
     * @return array<string, string>
     */
    public function elements(): array
    {
        return [
            '@title' => '.content-card-title',
            '@content-type' => '.content-type',
            '@use-button' => '.use-button',
            '@edit-button' => '.content-edit-button',
            '@details-button' => '.content-details-button',
            '@share-button' => '.share-button',
            '@copy-button' => '.content-copy-button',
            '@edit-link' => '.content-edit-link',
            '@views' => '.content-card-views',
            '@action-menu-toggle' => '.action-menu-toggle',
            '@action-menu' => '.action-menu-toggle + .dropdown-menu',
        ];
    }
}
