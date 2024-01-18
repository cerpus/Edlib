<?php

declare(strict_types=1);

namespace Tests\Browser;

use App\Jobs\RebuildContentIndex;
use App\Models\Content;
use App\Models\ContentVersion;
use App\Models\LtiPlatform;
use App\Models\LtiTool;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Laravel\Dusk\Browser;
use Tests\Browser\Components\ContentCard;
use Tests\Browser\Components\VersionHistory;
use Tests\DuskTestCase;

use function assert;

final class ContentTest extends DuskTestCase
{
    public function testListsMyContent(): void
    {
        $user = User::factory()->create();
        $content = Content::factory()
            ->withPublishedVersion()
            ->withUser($user)
            ->create();

        // FIXME: why doesn't indexing happen automatically?
        RebuildContentIndex::dispatchSync();

        $this->browse(function (Browser $browser) use ($content, $user) {
            $browser
                ->loginAs($user->email)
                ->assertAuthenticated()
                ->visit('/')
                ->clickLink('My content')
                ->assertTitleContains('My content')
                ->with(new ContentCard(), function (Browser $card) use ($content) {
                    $card->assertSeeIn('@title', $content->getTitle());
                })
                ->assertPresent('.content-card');
        });
    }

    public function testCountsViewsThroughDetailView(): void
    {
        $content = Content::factory()
            ->withPublishedVersion()
            ->create();

        $this->assertSame(0, $content->views()->count());

        $this->browse(fn (Browser $browser) => $browser
            ->visit('/content')
            ->with(
                new ContentCard(),
                fn (Browser $card) => $card
                ->assertSeeIn('@views', '0')
                ->click('@title')
            )
            ->visit('/content')
            ->with(
                new ContentCard(),
                fn (Browser $card) => $card
                ->assertSeeIn('@views', '1')
            ));

        $this->assertSame(1, $content->views()->count());

        $view = $content->views()->firstOrFail();
        $this->assertTrue($view->source->isDetail());
        $this->assertNotNull($view->ip);
    }

    public function testDoesNotAttemptToListContentWithoutVersions(): void
    {
        $user = User::factory()->create();
        Content::factory()->withUser($user)->create();

        // FIXME: why doesn't indexing happen automatically?
        RebuildContentIndex::dispatchSync();

        $this->browse(function (Browser $browser) use ($user) {
            $browser
                ->loginAs($user->email)
                ->assertAuthenticated()
                ->visit('/')
                ->clickLink('My content')
                ->assertTitleContains('My content')
                ->assertNotPresent('.content-card');
        });
    }

    public function testSeesNoContentCreatedYetMessageOnEmptySharedContentPage(): void
    {
        $this->browse(function (Browser $browser) {
            $browser
                ->visit('/')
                ->clickLink('Explore')
                ->with('.big-notice', function (Browser $message) {
                    $message
                        ->assertSee('No content has been created yet…')
                        ->assertSee('Try creating new content.')
                        ->assertNotPresent('a');
                });
        });
    }

    public function testSeesNoResultsMessageOnSharedContentPageWithQueryTyped(): void
    {
        $this->browse(function (Browser $browser) {
            $browser
                ->visit('/')
                ->clickLink('Explore')
                ->type('q', 'some keywords')
                ->pause(1200) // FIXME
                ->with('.big-notice', function (Browser $message) {
                    $message
                        ->assertSee('Sorry! No results found :(')
                        ->assertSee('We could not find any content based on your search. Try different keywords or filters.')
                        ->assertNotPresent('a');
                });
        });
    }

    public function testSeesNoContentCreatedYetMessageOnMyContentsPage(): void
    {
        $user = User::factory()->create();

        $this->browse(function (Browser $browser) use ($user) {
            $browser
                ->loginAs($user->email)
                ->assertAuthenticated()
                ->visit('/')
                ->clickLink('My content')
                ->with('.big-notice', function (Browser $message) {
                    $message
                        ->assertSee('You have no content yet…')
                        ->assertSee('Try exploring or creating new content.')
                        ->assertSeeIn('a:first-child', 'Explore content')
                        ->assertSeeIn('a:last-child', 'Create content');
                });
        });
    }

    public function testSeesNoResultsMessageOnMyContentPageWithQueryTyped(): void
    {
        $user = User::factory()->create();

        $this->browse(function (Browser $browser) use ($user) {
            $browser
                ->loginAs($user->email)
                ->assertAuthenticated()
                ->visit('/')
                ->clickLink('My content')
                ->type('q', 'some keywords')
                ->pause(1200) // FIXME
                ->with('.big-notice', function (Browser $message) {
                    $message
                        ->assertSee('Sorry! No results found :(')
                        ->assertSee('We could not find any content based on your search. Try different keywords or filters.')
                        ->assertSeeIn('a:first-child', 'Explore content')
                        ->assertSeeIn('a:last-child', 'Create content');
                });
        });
    }

    public function testLaunchesEdlibFromWithinEdlibAndSelectsContent(): void
    {
        $content = Content::factory()
            ->has(ContentVersion::factory()->published(), 'versions')
            ->create();

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

        $this->browse(function (Browser $browser) use ($content, $tool, $user) {
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
                ->withinFrame('.lti-launch', function (Browser $iframe) use ($content) {
                    $iframe->with(new ContentCard(), function (Browser $card) use ($content) {
                        $card
                            ->assertSeeIn('@title', $content->getTitle())
                            ->click('@use-button');
                    });
                })
                ->assertTitleContains($content->getTitle());
        });

        $this->assertDatabaseCount(Content::class, 2);
    }

    public function testHidesVersionHistoryToOutsiders(): void
    {
        $content = Content::factory()
            ->withPublishedVersion()
            ->create();

        $this->browse(function (Browser $browser) use ($content) {
            $browser
                ->visit('/content/' . $content->id)
                ->assertTitleContains($content->getTitle())
                ->assertNotPresent((new VersionHistory())->selector());
        });
    }

    public function testShowsVersionHistoryWhenOwnerOfContent(): void
    {
        $user = User::factory()->create();
        $content = Content::factory()
            ->withUser($user)
            ->withVersion(ContentVersion::factory()->published())
            ->withVersion(ContentVersion::factory()->unpublished())
            ->withVersion(ContentVersion::factory()->unpublished())
            ->withVersion(ContentVersion::factory()->unpublished())
            ->withVersion(ContentVersion::factory()->published())
            ->create();

        $this->browse(function (Browser $browser) use ($content, $user) {
            $browser
                ->loginAs($user->email)
                ->assertAuthenticated()
                ->visit('/content/' . $content->id)
                ->with(new VersionHistory(), function (Browser $history) {
                    $history
                        ->assertPresent('@version:nth-child(1).published')
                        ->assertPresent('@version:nth-child(2).draft')
                        ->assertPresent('@version:nth-child(3).draft')
                        ->assertPresent('@version:nth-child(4).draft')
                        ->assertPresent('@version:nth-child(5).published');
                });
        });
    }

    public function testPreviewsContent(): void
    {
        $content = Content::factory()
            ->withPublishedVersion()
            ->create()
            ->fresh(); // FIXME: why won't this work without?
        assert($content instanceof Content);

        $expectedTitle = $content->latestPublishedVersion?->title;
        assert($expectedTitle !== null);

        $this->browse(function (Browser $browser) use ($content, $expectedTitle) {
            $browser->visit('/content/'.$content->id)
                ->assertTitleContains($expectedTitle)
                ->assertPresent('iframe');
        });
    }

    public function testResizesIframeWhenRequestedByTool(): void
    {
        $platform = LtiPlatform::factory()->create();
        $tool = LtiTool::factory()->state([
            'creator_launch_url' => 'https://hub-test.edlib.test/lti/resize-test',
            'consumer_key' => $platform->key,
            'consumer_secret' => $platform->secret,
        ])->create();
        $user = User::factory()->create();

        $this->browse(function (Browser $browser) use ($tool, $user) {
            $browser
                ->loginAs($user->email)
                ->assertAuthenticated()
                ->visit('/content/create/' . $tool->id)
                ->assertPresent('.lti-launch')
                ->withinFrame('.lti-launch', fn (Browser $frame) => $frame->press('Resize to 640'))
                ->assertScript('document.querySelector(".lti-launch").scrollHeight', 640)
                ->withinFrame('.lti-launch', fn (Browser $frame) => $frame->press('Resize to 800'))
                ->assertScript('document.querySelector(".lti-launch").scrollHeight', 800)
            ;
        });
    }
}
