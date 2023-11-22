<?php

declare(strict_types=1);

namespace Tests\Browser;

use App\Models\Content;
use App\Models\ContentVersion;
use App\Models\LtiPlatform;
use App\Models\LtiTool;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Laravel\Dusk\Browser;
use Tests\Browser\Components\ContentCard;
use Tests\DuskTestCase;

use function assert;

final class ContentTest extends DuskTestCase
{
    public function testLaunchesEdlibFromWithinEdlibAndSelectsContent(): void
    {
        $content = Content::factory()
            ->has(ContentVersion::factory()->published(), 'versions')
            ->create();
        $contentTitle = $content->latestPublishedVersion?->resource?->title;
        assert($contentTitle !== null);

        $this->assertDatabaseCount(Content::class, 1);

        $user = User::factory()->create([
            'password' => Hash::make('password'),
            'admin' => true,
        ]);

        $platform = LtiPlatform::factory()->create();

        $tool = LtiTool::factory()->create([
            'name' => 'Edlib 3',
            'consumer_key' => $platform->key,
            'consumer_secret' => $platform->secret,
            'creator_launch_url' => route('lti.select'),
            'send_email' => true,
        ]);

        $this->browse(function (Browser $browser) use ($tool, $user, $contentTitle) {
            $browser
                ->visit('/login')
                // FIXME: it seems buttons in iframes cannot be clicked, even if
                // they are scrolled into view. We resize the window to get the
                // mobile view, where no scrolling is needed.
                ->resize(640, 800)
                ->type('email', $user->email)
                ->type('password', 'password')
                ->press('Log in')
                ->assertAuthenticated()
                ->visit('/content/create/' . $tool->id)
                ->assertPresent('.lti-launch')
                ->withinFrame('.lti-launch', function (Browser $iframe) use ($contentTitle) {
                    $iframe->with(new ContentCard(), function (Browser $card) use ($contentTitle) {
                        $card
                            ->assertSeeIn('@title', $contentTitle)
                            ->click('@use-button');
                    });
                })
                ->assertTitleContains($contentTitle);
        });

        $this->assertDatabaseCount(Content::class, 2);
    }

    public function testPreviewsContent(): void
    {
        $content = Content::factory()
            ->withPublishedVersion()
            ->create()
            ->fresh(); // FIXME: why won't this work without?
        assert($content instanceof Content);

        $expectedTitle = $content->latestPublishedVersion?->resource?->title;
        assert($expectedTitle !== null);

        $this->browse(function (Browser $browser) use ($content, $expectedTitle) {
            $browser->visit('/content/'.$content->id)
                ->assertTitleContains($expectedTitle)
                ->assertPresent('iframe');
        });
    }
}
