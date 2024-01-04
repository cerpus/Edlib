<?php

declare(strict_types=1);

namespace Tests\Feature;

use Tests\TestCase;

final class HomeTest extends TestCase
{
    public function testRecentContentIsShownToLoggedOutUsers(): void
    {
        $response = $this->get('/');

        $response
            ->assertStatus(200)
            ->assertSeeText('TODO: write the rest of the application')
        ;
    }
}
