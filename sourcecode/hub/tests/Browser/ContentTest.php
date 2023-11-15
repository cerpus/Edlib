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
                ->type('email', $user->email)
                ->type('password', 'password')
                ->press('Log in')
                ->assertAuthenticated()
                ->visit('/content/create/' . $tool->id)
                ->assertPresent('iframe')
                ->withinFrame('iframe', function (Browser $iframe) use ($contentTitle) {
                    $iframe->with('.content-card', function (Browser $card) use ($contentTitle) {
                        $card
                            ->assertSee($contentTitle)
                            ->press('Use content');
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
