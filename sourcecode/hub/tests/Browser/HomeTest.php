<?php

declare(strict_types=1);

namespace Tests\Browser;

use App\Models\Content;
use App\Models\User;
use Laravel\Dusk\Browser;
use Tests\Browser\Components\ContentCard;
use Tests\DuskTestCase;

final class HomeTest extends DuskTestCase
{
    public function testShowsRecentContentToLoggedOutUsers(): void
    {
        $latestTitle = Content::factory()
            ->withPublishedVersion()
            ->count(10)
            ->shared()
            ->create()
            ->first()?->getTitle() ?? $this->fail();

        $this->browse(function (Browser $browser) use ($latestTitle) {
            $browser
                ->visit('/')
                ->assertSee('Recent content')
                ->with(new ContentCard(), function (Browser $card) use ($latestTitle) {
                    $card->assertSeeIn('@title', $latestTitle);
                });
        });
    }

    public function testRedirectsLoggedInUsersToSharedContent(): void
    {
        $user = User::factory()->create();

        $this->browse(function (Browser $browser) use ($user) {
            $browser
                ->loginAs($user->email)
                ->assertAuthenticated()
                ->visit('/')
                ->assertUrlIs('https://hub-test.edlib.test/content');
        });
    }
}
