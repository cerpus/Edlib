<?php

declare(strict_types=1);

namespace Tests\Browser;

use App\Enums\ContentUserRole;
use App\Jobs\RebuildContentIndex;
use App\Models\Content;
use App\Models\ContentVersion;
use App\Models\LtiPlatform;
use App\Models\LtiTool;
use App\Models\User;
use Facebook\WebDriver\Chrome\ChromeDevToolsDriver;
use Illuminate\Support\Facades\Hash;
use Laravel\Dusk\Browser;
use Tests\Browser\Components\ContentCard;
use Tests\Browser\Components\PreviewModal;
use Tests\Browser\Components\VersionHistory;
use Tests\DuskTestCase;

use function assert;

final class ContentTest extends DuskTestCase
{
    public function testListsMyContent(): void
    {
        $user = User::factory()->create();
        $content = Content::factory()
            ->withUser($user)
            ->withPublishedVersion()
            ->create();

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
            ->shared()
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
                ->waitForEvent('htmx:after-swap')
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
                ->waitForEvent('htmx:after-swap')
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
            ->withPublishedVersion()
            ->shared()
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
                ->visit('/content/' . $content->id . '/history')
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
                ->visit('/content/' . $content->id . '/history')
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

    public function testPreviewsContentInDetails(): void
    {
        $content = Content::factory()
            ->withPublishedVersion()
            ->create();

        assert($content instanceof Content);

        $expectedTitle = $content->latestPublishedVersion?->title;
        assert($expectedTitle !== null);

        $this->browse(function (Browser $browser) use ($content, $expectedTitle) {
            $browser->visit('/content/'.$content->id)
                ->assertTitleContains($expectedTitle)
                ->assertPresent('iframe');
        });
    }

    public function testPreviewsContentInModal(): void
    {
        $content = Content::factory()
            ->withPublishedVersion()
            ->shared()
            ->create();

        assert($content instanceof Content);

        $this->browse(
            fn (Browser $browser) => $browser
                ->visit('/content')
                ->with(
                    new ContentCard(),
                    fn (Browser $card) => $card
                        ->click('@action-menu-toggle')
                        ->with(
                            '@action-menu',
                            fn (Browser $menu) => $menu
                                ->clickLink('Preview')
                        )
                )
                ->waitForEvent('htmx:after-swap')
                ->assertVisible('#previewModal .lti-launch')
        );
    }

    public function testUsesContentViaButtonInPreviewModal(): void
    {
        $ltiPlatform = LtiPlatform::factory()->create();
        $ltiTool = LtiTool::factory()
            ->state(['creator_launch_url' => 'https://hub-test.edlib.test/lti/dl'])
            ->withCredentials($ltiPlatform->getOauth1Credentials())
            ->create();
        $user = User::factory()->create();

        $content = Content::factory()
            ->withPublishedVersion()
            ->shared()
            ->create();

        $this->browse(
            fn (Browser $browser) => $browser
                ->loginAs($user->email)
                ->visit('/content/create/' . $ltiTool->id)
                ->withinFrame(
                    '.lti-launch',
                    fn (Browser $launch) => $launch
                        ->with(new ContentCard(), fn (Browser $card) => $card->click('@title'))
                        ->waitFor('#previewModal .modal-dialog')
                        ->with(
                            new PreviewModal(),
                            fn (Browser $modal) => $modal
                                ->click('@use-button')
                        )
                )
                ->assertTitleContains($content->getTitle())
        );
    }

    public function testPreviewModalHasNoUseButtonOutsideOfLtiContext(): void
    {
        Content::factory()->withPublishedVersion()->shared()->create();
        $user = User::factory()->create();

        $this->browse(
            fn (Browser $browser) => $browser
                ->loginAs($user->email)
                ->visit('/content')
                ->with(
                    new ContentCard(),
                    fn (Browser $card) => $card
                        ->click('@action-menu-toggle')
                        ->with(
                            '@action-menu',
                            fn (Browser $menu) => $menu
                                ->clickLink('Preview')
                        )
                )
                ->waitFor('#previewModal .modal-dialog')
                ->with(
                    new PreviewModal(),
                    fn (Browser $modal) => $modal
                        ->assertMissing('@use-button')
                )
        );
    }

    public function testResizesIframeWhenRequestedByTool(): void
    {
        $platform = LtiPlatform::factory()->create();
        $content = Content::factory()->withVersion(
            ContentVersion::factory()
                ->withLaunchUrl('https://hub-test.edlib.test/lti/samples/resize')
                ->tool(LtiTool::factory()->withCredentials($platform->getOauth1Credentials()))
                ->published()
        )->create();

        $this->browse(function (Browser $browser) use ($content) {
            $browser
                ->resize(1000, 1000)
                ->visit('/content/' . $content->id)
                ->assertPresent('.lti-launch')
                ->withinFrame('.lti-launch', fn (Browser $frame) => $frame->press('Resize to 640'))
                ->assertScript('document.querySelector(".lti-launch").scrollHeight', 640)
                ->withinFrame('.lti-launch', fn (Browser $frame) => $frame->press('Resize to 800'))
                ->assertScript('document.querySelector(".lti-launch").scrollHeight', 800)
            ;
        });
    }

    /**
     * Requests to ajax endpoints in LTI context must be aware of the session
     * scope, otherwise on the page will be replaced with HTML generated for the
     * outer session. This problem most obviously manifests itself as the 'use'
     * button missing on content cards after a search, so we check that they are
     * still present after performing one.
     */
    public function testContentCardsHaveUseButtonAfterSearchingInLtiContext(): void
    {
        $platform = LtiPlatform::factory()->create();
        $tool = LtiTool::factory()
            ->state(['creator_launch_url' => route('lti.select')])
            ->withCredentials($platform->getOauth1Credentials())
            ->create();

        Content::factory()->withVersion(
            ContentVersion::factory()
                ->state(['title' => 'found content'])
                ->published(),
        )->shared()->create();

        Content::factory()->withVersion(
            ContentVersion::factory()
                ->state(['title' => 'excluded content'])
                ->published(),
        )->shared()->create();

        $this->browse(fn (Browser $browser) => $browser
            ->loginAs(User::factory()->create()->email)
            ->assertAuthenticated()
            ->visit('/content/create/' . $tool->id)
            ->withinFrame('.lti-launch', fn (Browser $frame) => $frame
                ->assertSee('found content')
                ->assertSee('excluded content')
                ->type('q', 'found')
                ->waitForEvent('htmx:after-swap')
                ->assertSee('found content')
                ->assertDontSee('excluded content')
                ->with(
                    new ContentCard(),
                    fn (Browser $card) => $card
                        ->assertPresent('@use-button'),
                )));
    }

    public function testCanDeleteOwnContent(): void
    {
        $user = User::factory()->create();

        $content = Content::factory()
            ->withUser($user)
            ->withPublishedVersion()
            ->create();
        $this->assertFalse($content->trashed());

        $this->browse(
            fn (Browser $browser) => $browser
                ->loginAs($user->email)
                ->assertAuthenticated()
                ->visit('/content/' . $content->id)
                ->click('.delete-content-button')
                ->waitFor('#htmxConfirmModal-Ok')
                ->click('#htmxConfirmModal-Ok')
                ->waitForLocation('/content/mine')
                ->assertPresent('.toast-container')
        );

        $this->assertTrue($content->refresh()->trashed());
    }

    public function testCannotDeleteSomeoneElsesContent(): void
    {
        $content = Content::factory()
            ->withPublishedVersion()
            ->create();

        $this->browse(
            fn (Browser $browser) => $browser
                ->loginAs(User::factory()->create()->email)
                ->assertAuthenticated()
                ->visit('/content/' . $content->id)
                ->assertTitleContains($content->getTitle())
                ->assertNotPresent('.delete-content-button')
        );
    }

    public function testCreatesDraftVersions(): void
    {
        $platform = LtiPlatform::factory()->create();
        $tool = LtiTool::factory()
            ->withCredentials($platform->getOauth1Credentials())
            ->create();

        $this->browse(
            fn (Browser $browser) => $browser
                ->loginAs(User::factory()->create()->email)
                ->assertAuthenticated()
                ->visit('/content/create/' . $tool->id)
                ->withinFrame(
                    '.lti-launch',
                    fn (Browser $browser) => $browser
                        ->type('payload', <<<EOJSON
                        {
                            "@context": ["http://purl.imsglobal.org/ctx/lti/v1/ContentItem", {
                                "edlib": "https://spec.edlib.com/lti/vocab#",
                                "xs": "http://www.w3.org/2001/XMLSchema#",
                                "published": {
                                    "@id": "edlib:published",
                                    "@type": "xs:boolean"
                                }
                            }],
                            "@graph": [
                                {
                                    "@type": "LtiLinkItem",
                                    "mediaType": "application/vnd.ims.lti.v1.ltilink",
                                    "url": "https://hub-test.edlib.test/lti/samples/presentation",
                                    "title": "It should be a draft",
                                    "published": false
                                }
                            ]
                        }
                        EOJSON)
                        ->press('Send')
                )
                ->assertTitleContains('It should be a draft')
                ->assertSee('You are viewing an unpublished draft version.')
        );
    }

    public function testCreatesContentWithContentTypeTag(): void
    {
        $platform = LtiPlatform::factory()->create();
        $tool = LtiTool::factory()
            ->withCredentials($platform->getOauth1Credentials())
            ->create();

        $this->browse(
            fn (Browser $browser) => $browser
                ->loginAs(User::factory()->create()->email)
                ->assertAuthenticated()
                ->visit('/content/create/' . $tool->id)
                ->withinFrame(
                    '.lti-launch',
                    fn (Browser $browser) => $browser
                        ->type('payload', <<<EOJSON
                        {
                            "@context": ["http://purl.imsglobal.org/ctx/lti/v1/ContentItem", {
                                "edlib": "https://spec.edlib.com/lti/vocab#",
                                "xs": "http://www.w3.org/2001/XMLSchema#",
                                "tag": {
                                    "@id": "edlib:tag",
                                    "@type": "xs:normalizedString"
                                }
                            }],
                            "@graph": [
                                {
                                    "@type": "LtiLinkItem",
                                    "mediaType": "application/vnd.ims.lti.v1.ltilink",
                                    "url": "https://hub-test.edlib.test/lti/samples/presentation",
                                    "title": "TMK Course Presentation",
                                    "tag": "h5p:H5P.CoursePresentation"
                                }
                            ]
                        }
                        EOJSON)
                        ->press('Send')
                )
                ->assertTitleContains('TMK Course Presentation')
                ->visit('/content')
                ->with(
                    new ContentCard(),
                    fn (Browser $card) => $card
                        ->assertSeeIn('@title', 'TMK Course Presentation')
                        ->assertSeeIn('@content-type', 'H5P.CoursePresentation')
                )
        );
    }

    public function testUserCanDisableSharingContent(): void
    {
        $user = User::factory()->create();
        $content = Content::factory()
            ->withUser($user)
            ->withPublishedVersion()
            ->shared()
            ->create();

        $this->browse(
            fn (Browser $browser) => $browser
                ->loginAs($user->email)
                ->assertAuthenticated()
                ->visit('/content')
                ->with(
                    new ContentCard(),
                    fn (Browser $card) => $card
                        ->assertSeeIn('@title', $content->getTitle())
                        ->click('@title')
                )
                ->click('#shared-toggle')
                ->waitForEvent('htmx:afterRequest')
                ->visit('/content')
                ->assertNotPresent('.content-card')
        );
    }

    public function testCanToggleResultView(): void
    {
        Content::factory()
            ->withPublishedVersion()
            ->shared()
            ->create();

        $this->browse(
            fn (Browser $browser) => $browser
                ->visit('/content')
                ->assertPresent('article.card.content-card')
                ->press('button.btn-outline-secondary[title="Display results as list"]')
                ->waitForLocation('/content')
                ->assertPresent('article.card.content-list-item')
                ->press('button.btn-outline-secondary[title="Display results as grid"]')
                ->waitForLocation('/content')
                ->assertPresent('article.card.content-card')
        );
    }

    public function testCanCopySharedContent(): void
    {
        $content = Content::factory()
            ->withPublishedVersion()
            ->shared()
            ->create();

        RebuildContentIndex::dispatchSync();

        $this->browse(
            fn (Browser $browser) => $browser
                ->loginAs(User::factory()->create()->email)
                ->assertAuthenticated()
                ->visit('/content')
                ->with(
                    new ContentCard(),
                    fn (Browser $card) => $card
                        ->assertSeeIn('@title', $content->getTitle())
                        ->click('@action-menu-toggle')
                        ->with(
                            '@action-menu',
                            fn (Browser $menu) => $menu
                                ->press('Copy')
                        )
                )
                ->assertTitleContains($content->getTitle() . ' (copy)')
                ->assertSee('You are viewing an unpublished draft version')
                ->pause(500) // FIXME: indexing should be synchronous in tests
                ->visit('/content/mine')
                ->with(
                    new ContentCard(),
                    fn (Browser $card) => $card
                        ->assertSeeIn('@title', $content->getTitle() . ' (copy)')
                )
        );
    }

    public function testCanCopyOwnContent(): void
    {
        $user = User::factory()->create();
        $content = Content::factory()
            ->withUser($user)
            ->withPublishedVersion()
            ->shared(false)
            ->create();

        RebuildContentIndex::dispatchSync();

        $this->browse(
            fn (Browser $browser) => $browser
                ->loginAs($user->email)
                ->assertAuthenticated()
                ->visit('/content/mine')
                ->with(
                    new ContentCard(),
                    fn (Browser $card) => $card
                        ->assertSeeIn('@title', $content->getTitle())
                        ->click('@action-menu-toggle')
                        ->with(
                            '@action-menu',
                            fn (Browser $menu) => $menu
                                ->press('Copy')
                        )
                )
                ->assertTitleContains($content->getTitle() . ' (copy)')
                ->assertSee('You are viewing an unpublished draft version')
        );
    }

    public function testSharingCopiesUrl(): void
    {
        $content = Content::factory()->withPublishedVersion()->create();

        $this->browse(function (Browser $browser) use ($content) {
            $devTools = (new ChromeDevToolsDriver($browser->driver));
            $devTools->execute('Browser.grantPermissions', [
                'permissions' => ['clipboardReadWrite'],
            ]);

            $browser
                ->visit('/content/' . $content->id)
                ->clickLink('Share')
                ->assertDialogOpened('The address for sharing has been copied to your clipboard.')
                ->acceptDialog()
                ->assertPathIs('/content/' . $content->id)
                ->assertScript(
                    'navigator.clipboard.readText()',
                    'https://hub-test.edlib.test/c/' . $content->id,
                );
        });
    }

    public function testViewsContentRoles(): void
    {
        $content = Content::factory()
            ->withUser(User::factory()->name('Owner McOwnerson'), ContentUserRole::Owner)
            ->withUser(User::factory()->name('Editor McEditorson'), ContentUserRole::Editor)
            ->withUser(User::factory()->name('Reader McReaderson'), ContentUserRole::Reader)
            ->withPublishedVersion()
            ->create();

        $this->browse(
            fn (Browser $browser) => $browser
                ->loginAs(User::factory()->admin()->create()->email)
                ->assertAuthenticated()
                ->visit('/content/' . $content->id . '/roles')
                ->with(
                    'main table tbody',
                    fn (Browser $tbody) => $tbody
                        ->with(
                            'tr:nth-child(1)',
                            fn (Browser $row) => $row
                                ->assertSeeIn('td:nth-child(1)', 'Owner McOwnerson')
                                ->assertSeeIn('td:nth-child(2)', 'Owner')
                        )
                        ->with(
                            'tr:nth-child(2)',
                            fn (Browser $row) => $row
                                ->assertSeeIn('td:nth-child(1)', 'Editor McEditorson')
                                ->assertSeeIn('td:nth-child(2)', 'Editor')
                        )
                        ->with(
                            'tr:nth-child(3)',
                            fn (Browser $row) => $row
                                ->assertSeeIn('td:nth-child(1)', 'Reader McReaderson')
                                ->assertSeeIn('td:nth-child(2)', 'Reader')
                        )
                )
        );
    }
}
