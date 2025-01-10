<?php

declare(strict_types=1);

namespace Tests\Browser;

use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

final class NdlaTest extends DuskTestCase
{
    public function testLoadsSwagger(): void
    {
        $this->browse(
            fn(Browser $browser) => $browser
                ->visit('https://hub-test-ndla-legacy.edlib.test/swagger')
                ->waitForTextIn('h2.title', 'Edlib facade'),
        );
    }
}
