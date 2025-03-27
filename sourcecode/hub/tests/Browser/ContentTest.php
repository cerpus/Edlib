<?php

declare(strict_types=1);

namespace Tests\Browser;

use App\Enums\ContentRole;
use App\Enums\LtiToolEditMode;
use App\Jobs\RebuildContentIndex;
use App\Models\Content;
use App\Models\ContentVersion;
use App\Models\ContentView;
use App\Models\Context;
use App\Models\LtiPlatform;
use App\Models\LtiTool;
use App\Models\LtiToolExtra;
use App\Models\User;
use Carbon\Carbon;
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

        $this->browse(fn(Browser $browser) => $browser
            ->visit('/content')
            ->with(
                new ContentCard(),
                fn(Browser $card) => $card
                    ->assertSeeIn('@views', '0')
                    ->click('@details-button'),
            )
            ->visit('/content')
            ->with(
                new ContentCard(),
                fn(Browser $card) => $card
                    ->assertSeeIn('@views', '1'),
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

        LtiTool::factory()->slug('the-tool')->create([
            'name' => 'Edlib 3',
            'consumer_key' => $platform->key,
            'consumer_secret' => $platform->secret,
            'creator_launch_url' => route('lti.select'),
            'send_email' => true,
        ]);

        $this->browse(function (Browser $browser) use ($content, $user) {
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
                ->visit('/content/create/the-tool')
                ->assertPresent('.lti-launch')
                ->withinFrame('.lti-launch', function (Browser $iframe) use ($content) {
                    $iframe
                        ->waitFor('article.content-card')
                        ->with(new ContentCard(), function (Browser $card) use ($content) {
                            $card
                                ->assertSeeIn('@title', $content->getTitle())
                                ->click('@use-button');
                        });
                })
                ->waitForText($content->getTitle())
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
            $browser->visit('/content/' . $content->id)
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
            fn(Browser $browser) => $browser
                ->visit('/content')
                ->with(
                    new ContentCard(),
                    fn(Browser $card) => $card
                        ->click('@title'),
                )
                ->waitFor('.preview-modal')
                ->assertVisible('.preview-modal .lti-launch'),
        );
    }

    public function testUsesContentViaButtonInPreviewModal(): void
    {
        $ltiPlatform = LtiPlatform::factory()->create();
        LtiTool::factory()
            ->slug('the-tool')
            ->state(['creator_launch_url' => 'https://hub-test.edlib.test/lti/dl'])
            ->withCredentials($ltiPlatform->getOauth1Credentials())
            ->create();
        $user = User::factory()->create();

        $content = Content::factory()
            ->withPublishedVersion()
            ->shared()
            ->create();

        $this->browse(
            fn(Browser $browser) => $browser
                ->loginAs($user->email)
                ->visit('/content/create/the-tool')
                ->waitFor('iframe.lti-launch')
                ->withinFrame(
                    '.lti-launch',
                    fn(Browser $launch) => $launch
                        ->waitFor('article.content-card')
                        ->with(new ContentCard(), fn(Browser $card) => $card->click('@title'))
                        ->waitFor('.preview-modal')
                        ->with(
                            new PreviewModal(),
                            fn(Browser $modal) => $modal
                                ->click('@use-button'),
                        ),
                )
                ->waitForText($content->getTitle())
                ->assertTitleContains($content->getTitle()),
        );
    }

    public function testPreviewModalHasNoUseButtonOutsideOfLtiContext(): void
    {
        Content::factory()->withPublishedVersion()->shared()->create();
        $user = User::factory()->create();

        $this->browse(
            fn(Browser $browser) => $browser
                ->loginAs($user->email)
                ->visit('/content')
                ->with(
                    new ContentCard(),
                    fn(Browser $card) => $card
                        ->click('@title'),
                )
                ->waitFor('.preview-modal')
                ->with(
                    new PreviewModal(),
                    fn(Browser $modal) => $modal
                        ->assertMissing('@use-button'),
                ),
        );
    }

    public function testResizesIframeWhenRequestedByTool(): void
    {
        $platform = LtiPlatform::factory()->create();
        $content = Content::factory()->withVersion(
            ContentVersion::factory()
                ->withLaunchUrl('https://hub-test.edlib.test/lti/samples/resize')
                ->tool(LtiTool::factory()->withCredentials($platform->getOauth1Credentials()))
                ->published(),
        )->create();

        $this->browse(function (Browser $browser) use ($content) {
            $browser
                ->resize(1000, 1000)
                ->visit('/content/' . $content->id)
                ->assertPresent('.lti-launch')
                ->withinFrame('.lti-launch', fn(Browser $frame) => $frame->press('Resize to 640'))
                ->assertScript('document.querySelector(".lti-launch").scrollHeight', 640)
                ->withinFrame('.lti-launch', fn(Browser $frame) => $frame->press('Resize to 800'))
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
        LtiTool::factory()
            ->slug('the-tool')
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

        $this->browse(fn(Browser $browser) => $browser
            ->loginAs(User::factory()->create()->email)
            ->assertAuthenticated()
            ->visit('/content/create/the-tool')
            ->withinFrame('.lti-launch', fn(Browser $frame) => $frame
                ->assertSee('found content')
                ->assertSee('excluded content')
                ->type('q', 'found')
                ->waitForEvent('htmx:after-swap')
                ->assertSee('found content')
                ->assertDontSee('excluded content')
                ->with(
                    new ContentCard(),
                    fn(Browser $card) => $card
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
            fn(Browser $browser) => $browser
                ->loginAs($user->email)
                ->assertAuthenticated()
                ->visit('/content/' . $content->id)
                ->click('.delete-content-button')
                ->waitFor('#htmxConfirmModal-Ok')
                ->click('#htmxConfirmModal-Ok')
                ->waitForLocation('/content/mine')
                ->assertPresent('.toast-container'),
        );

        $this->assertTrue($content->refresh()->trashed());
    }

    public function testCannotDeleteSomeoneElsesContent(): void
    {
        $content = Content::factory()
            ->withPublishedVersion()
            ->create();

        $this->browse(
            fn(Browser $browser) => $browser
                ->loginAs(User::factory()->create()->email)
                ->assertAuthenticated()
                ->visit('/content/' . $content->id)
                ->assertTitleContains($content->getTitle())
                ->assertNotPresent('.delete-content-button'),
        );
    }

    public function testCreatesDraftVersions(): void
    {
        $platform = LtiPlatform::factory()->create();
        LtiTool::factory()
            ->slug('the-tool')
            ->withCredentials($platform->getOauth1Credentials())
            ->create();

        $this->browse(
            fn(Browser $browser) => $browser
                ->loginAs(User::factory()->create()->email)
                ->assertAuthenticated()
                ->visit('/content/create/the-tool')
                ->withinFrame(
                    '.lti-launch',
                    fn(Browser $browser) => $browser
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
                        ->press('Send'),
                )
                ->waitFor('main div h1')
                ->assertTitleContains('It should be a draft')
                ->assertSee('You are viewing an unpublished draft version.'),
        );
    }

    public function testCreatesContentWithContentTypeTag(): void
    {
        $platform = LtiPlatform::factory()->create();
        LtiTool::factory()
            ->slug('the-tool')
            ->withCredentials($platform->getOauth1Credentials())
            ->create();

        $this->browse(
            fn(Browser $browser) => $browser
                ->loginAs(User::factory()->create()->email)
                ->assertAuthenticated()
                ->visit('/content/create/the-tool')
                ->withinFrame(
                    '.lti-launch',
                    fn(Browser $browser) => $browser
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
                        ->press('Send'),
                )
                ->waitFor('main div h1')
                ->assertTitleContains('TMK Course Presentation')
                ->visit('/content')
                ->with(
                    new ContentCard(),
                    fn(Browser $card) => $card
                        ->assertSeeIn('@title', 'TMK Course Presentation')
                        ->assertSeeIn('@content-type', 'H5P.CoursePresentation'),
                ),
        );
    }

    public function testEnsuresContentReturnedToLtiPlatformIsPublished(): void
    {
        $platform = LtiPlatform::factory()->create();
        LtiTool::factory()
            ->slug('edlib-3')
            ->withName('Edlib 3')
            ->withCredentials($platform->getOauth1Credentials())
            ->launchUrl('https://hub-test.edlib.test/lti/dl')
            ->state(['send_name' => true, 'send_email' => true])
            ->create();
        $ltiTool = LtiTool::factory()
            ->slug('sample-tool')
            ->withName('Sample tool')
            ->withCredentials($platform->getOauth1Credentials())
            ->launchUrl('https://hub-test.edlib.test/lti/samples/deep-link')
            ->state(['send_name' => true, 'send_email' => true])
            ->create();
        $url = route('content.launch-creator', [$ltiTool]);

        $this->browse(
            fn(Browser $browser) => $browser
                ->loginAs(User::factory()->create()->email)
                ->assertAuthenticated()
                ->visit('/content/create/edlib-3')
                ->waitFor('iframe.lti-launch')
                ->withinFrame(
                    '.lti-launch',
                    fn(Browser $e3Frame) => $e3Frame
                        ->clickLink('Create')
                        ->waitForText('Select a content type')
                        ->click('a[href^="' . $url . '"]')
                        ->waitFor('iframe.lti-launch')
                        ->withinFrame(
                            '.lti-launch',
                            fn(Browser $ltiTool) => $ltiTool
                                ->type('payload', <<<EOJSON
                    {
                        "@context": ["http://purl.imsglobal.org/ctx/lti/v1/ContentItem", {
                            "edlib": "https://spec.edlib.com/lti/vocab#",
                            "xs": "http://www.w3.org/2001/XMLSchema#",
                            "published": {
                                "@id": "edlib:tag",
                                "@type": "xs:boolean"
                            }
                        }],
                        "@graph": [
                            {
                                "@type": "LtiLinkItem",
                                "mediaType": "application/vnd.ims.lti.v1.ltilink",
                                "url": "https://hub-test.edlib.test/lti/samples/presentation",
                                "title": "TMK Course Presentation",
                                "published": false
                            }
                        ]
                    }
                    EOJSON)
                                ->press('Send'),
                        ),
                )
                ->waitForReload()
                ->assertTitleContains('TMK Course Presentation')
                ->assertDontSee('You are viewing an unpublished draft version.'),
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
            fn(Browser $browser) => $browser
                ->loginAs($user->email)
                ->assertAuthenticated()
                ->visit('/content')
                ->with(
                    new ContentCard(),
                    fn(Browser $card) => $card
                        ->assertSeeIn('@title', $content->getTitle())
                        ->click('@details-button'),
                )
                ->click('#shared-toggle')
                ->waitForEvent('htmx:afterRequest')
                ->visit('/content')
                ->assertNotPresent('.content-card'),
        );
    }

    public function testCanToggleResultView(): void
    {
        Content::factory()
            ->withPublishedVersion()
            ->shared()
            ->create();

        $this->browse(
            fn(Browser $browser) => $browser
                ->visit('/content')
                ->assertPresent('article.card.content-card')
                ->press('button.btn-outline-secondary[title="Display results as list"]')
                ->waitForLocation('/content')
                ->assertPresent('article.card.content-list-item')
                ->press('button.btn-outline-secondary[title="Display results as grid"]')
                ->waitForLocation('/content')
                ->assertPresent('article.card.content-card'),
        );
    }

    public function testCanCopySharedContent(): void
    {
        $content = Content::factory()
            ->withVersion(
                ContentVersion::factory()
                    ->published()
                    ->withTag('h5p:h5p.coursepresentation', 'H5P.CoursePresentation'),
            )
            ->shared()
            ->create();

        $this->browse(
            fn(Browser $browser) => $browser
                ->loginAs(User::factory()->create()->email)
                ->assertAuthenticated()
                ->visit('/content')
                ->with(
                    new ContentCard(),
                    fn(Browser $card) => $card
                        ->assertSeeIn('@title', $content->getTitle())
                        ->click('@copy-button'),
                )
                ->assertTitleContains($content->getTitle() . ' (copy)')
                ->assertSee('You are viewing an unpublished draft version')
                ->assertSeeIn('aside table', 'H5P.CoursePresentation')
                ->visit('/content/mine')
                ->with(
                    new ContentCard(),
                    fn(Browser $card) => $card
                        ->assertSeeIn('@title', $content->getTitle() . ' (copy)'),
                ),
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

        $this->browse(
            fn(Browser $browser) => $browser
                ->loginAs($user->email)
                ->assertAuthenticated()
                ->visit('/content/mine')
                ->with(
                    new ContentCard(),
                    fn(Browser $card) => $card
                        ->assertSeeIn('@title', $content->getTitle())
                        ->click('@action-menu-toggle')
                        ->with(
                            '@action-menu',
                            fn(Browser $menu) => $menu
                                ->press('Copy'),
                        ),
                )
                ->assertTitleContains($content->getTitle() . ' (copy)')
                ->assertSee('You are viewing an unpublished draft version'),
        );
    }

    public function testSharingCopiesUrl(): void
    {
        $content = Content::factory()->withPublishedVersion()->create();

        $this->browse(function (Browser $browser) use ($content) {
            $browser
                ->visit('/content/' . $content->id)
                ->clickLink('Share')
                ->waitFor('.share-dialog')
                ->click('.copy-to-clipboard')
                ->assertDialogOpened('The address for sharing has been copied to your clipboard.')
                ->acceptDialog();

            $devTools = (new ChromeDevToolsDriver($browser->driver));
            $devTools->execute('Browser.grantPermissions', [
                'permissions' => ['clipboardReadWrite'],
            ]);

            $browser->assertScript(
                'navigator.clipboard.readText()',
                'https://hub-test.edlib.test/c/' . $content->id,
            );
        });
    }

    public function testViewsContentRoles(): void
    {
        $content = Content::factory()
            ->withUser(User::factory()->name('Owner McOwnerson'), ContentRole::Owner)
            ->withUser(User::factory()->name('Editor McEditorson'), ContentRole::Editor)
            ->withUser(User::factory()->name('Reader McReaderson'), ContentRole::Reader)
            ->withPublishedVersion()
            ->create();

        $this->browse(
            fn(Browser $browser) => $browser
                ->loginAs(User::factory()->admin()->create()->email)
                ->assertAuthenticated()
                ->visit('/content/' . $content->id . '/roles')
                ->with(
                    'main table tbody',
                    fn(Browser $tbody) => $tbody
                        ->with(
                            'tr:nth-child(1)',
                            fn(Browser $row) => $row
                                ->assertSeeIn('td:nth-child(1)', 'Owner McOwnerson')
                                ->assertSeeIn('td:nth-child(2)', 'Owner'),
                        )
                        ->with(
                            'tr:nth-child(2)',
                            fn(Browser $row) => $row
                                ->assertSeeIn('td:nth-child(1)', 'Editor McEditorson')
                                ->assertSeeIn('td:nth-child(2)', 'Editor'),
                        )
                        ->with(
                            'tr:nth-child(3)',
                            fn(Browser $row) => $row
                                ->assertSeeIn('td:nth-child(1)', 'Reader McReaderson')
                                ->assertSeeIn('td:nth-child(2)', 'Reader'),
                        ),
                ),
        );
    }

    public function testCanSeeStatisticsOnDetailsPage(): void
    {
        $content = Content::factory()
            ->withPublishedVersion()
            ->shared()
            ->create();

        ContentView::factory()
            ->create([
                'content_id' => $content->id,
            ]);

        $this->browse(
            fn(Browser $browser) => $browser
                ->visit('/content/' . $content->id . '/statistics')
                ->assertPresent('#chart_usage svg g.bb-main')
                ->assertPresent('#chart_usage svg g.bb-bars-total')
                ->assertPresent('#chart_usage svg g.bb-texts-total')
                ->assertSeeIn('#chart_usage svg g.bb-texts-total text.bb-text-0', '1'),
        );
    }

    public function testDateIsFormattedAndLocalised(): void
    {
        $user = User::factory()->create(['locale' => 'nb']);

        $content = Content::factory()
            ->withVersion(
                ContentVersion::factory()->state([
                    'published' => true,
                    'created_at' => '2024-10-09 13:30:37',
                ])->published(),
            )
            ->shared()
            ->create();

        $contentVersion = $content->latestVersion;
        $this->assertNotNull($contentVersion);
        /** @var Carbon $date */
        $date = $contentVersion->created_at;
        $date->locale('nb_NO');

        $this->browse(
            fn(Browser $browser) => $browser
                ->loginAs($user->email)
                ->assertAuthenticated()
                ->visit('/content/')
                ->assertSeeIn('article.card', $contentVersion->title)
                ->assertSeeIn('article time', $date->translatedFormat('j. F Y'))
                ->assertAttribute('article time', 'datetime', $date->toIso8601String())
                ->assertAttribute('article time', 'title', $date->translatedFormat('l j. F Y \k\l. H:i:s')),
        );
    }

    public function testEditsContentViaLti(): void
    {
        $user = User::factory()->create();
        $platform = LtiPlatform::factory()->create();

        Content::factory()
            ->withVersion(
                ContentVersion::factory()
                    ->withLaunchUrl('https://hub-test.edlib.test/lti/samples/presentation')
                    ->published()
                    ->tool(
                        LtiTool::factory()
                            ->editMode(LtiToolEditMode::Replace)
                            ->withCredentials($platform->getOauth1Credentials()),
                    )
                    ->title('The original content'),
            )
            ->withUser($user)
            ->create();

        RebuildContentIndex::dispatch();

        $this->browse(
            fn(Browser $browser) => $browser
                ->loginAs($user->email)
                ->assertAuthenticated()
                ->visit('/lti/playground')
                ->type('launch_url', 'https://hub-test.edlib.test/lti/dl')
                ->type('key', $platform->key)
                ->type('secret', $platform->secret)
                ->type(
                    'parameters',
                    'content_item_return_url=https://hub-test.edlib.test/lti/dl' .
                    '&lti_message_type=ContentItemSelectionRequest' .
                    "&lis_person_contact_email_primary={$user->email}",
                )
                ->press('Launch')
                ->withinFrame(
                    'iframe',
                    fn(Browser $hub) => $hub
                        ->clickLink('My content')
                        ->clickLink('The original content')
                        ->waitForLink('Edit content')
                        ->clickLink('Edit content')
                        ->waitFor('iframe.lti-launch')
                        ->withinFrame(
                            '.lti-launch',
                            fn(Browser $editor) => $editor
                                ->waitForInput('payload')
                                ->type('payload', <<<EOJSON
                    {
                        "@context": "http://purl.imsglobal.org/ctx/lti/v1/ContentItem",
                        "@graph": [
                            {
                                "@type": "LtiLinkItem",
                                "mediaType": "application/vnd.ims.lti.v1.ltilink",
                                "url": "https://hub-test.edlib.test/lti/samples/presentation?v=2",
                                "title": "The updated content"
                            }
                        ]
                    }
                    EOJSON)
                                ->press('Send'),
                        )
                        ->withinFrame(
                            'iframe[name="launch_frame"]',
                            fn(Browser $result) => $result
                                ->waitFor('#content'),
                        ),
                )
                ->visit('/content/mine')
                ->assertSeeLink('The updated content')
                ->assertDontSee('The original content'),
        );
    }

    public function testCopiesContentWhenUpdatingWithCopyFlagViaLti(): void
    {
        $user = User::factory()->create();
        $platform = LtiPlatform::factory()->create();

        Content::factory()
            ->withVersion(
                ContentVersion::factory()
                    ->withLaunchUrl('https://hub-test.edlib.test/lti/samples/presentation')
                    ->published()
                    ->tool(
                        LtiTool::factory()
                            ->editMode(LtiToolEditMode::Replace)
                            ->withCredentials($platform->getOauth1Credentials()),
                    )
                    ->title('The original content'),
            )
            ->withUser($user)
            ->create();

        RebuildContentIndex::dispatch();

        $this->browse(
            fn(Browser $browser) => $browser
                ->loginAs($user->email)
                ->assertAuthenticated()
                ->visit('/lti/playground')
                ->type('launch_url', 'https://hub-test.edlib.test/lti/dl')
                ->type('key', $platform->key)
                ->type('secret', $platform->secret)
                ->type(
                    'parameters',
                    'content_item_return_url=https://hub-test.edlib.test/lti/dl' .
                    '&ext_edlib3_copy_before_save=1' .
                    '&lti_message_type=ContentItemSelectionRequest' .
                    "&lis_person_contact_email_primary={$user->email}",
                )
                ->press('Launch')
                ->withinFrame(
                    'iframe',
                    fn(Browser $hub) => $hub
                        ->clickLink('My content')
                        ->clickLink('The original content')
                        ->waitForLink('Edit content')
                        ->clickLink('Edit content')
                        ->waitFor('iframe.lti-launch')
                        ->withinFrame(
                            '.lti-launch',
                            fn(Browser $editor) => $editor
                                ->waitForInput('payload')
                                ->type('payload', <<<EOJSON
                    {
                        "@context": "http://purl.imsglobal.org/ctx/lti/v1/ContentItem",
                        "@graph": [
                            {
                                "@type": "LtiLinkItem",
                                "mediaType": "application/vnd.ims.lti.v1.ltilink",
                                "url": "https://hub-test.edlib.test/lti/samples/presentation?v=2",
                                "title": "The updated content"
                            }
                        ]
                    }
                    EOJSON)
                                ->press('Send'),
                        )
                        ->withinFrame(
                            'iframe[name="launch_frame"]',
                            fn(Browser $result) => $result
                                ->waitFor('#content'),
                        ),
                )
                ->visit('/content/mine')
                ->assertSeeLink('The updated content')
                ->assertSeeLink('The original content'),
        );
    }

    public function testOwnerOfContentCanAssignContextToContent(): void
    {
        $user = User::factory()->create();

        Context::factory()->name('filler')->create();
        $context = Context::factory()->name('desired')->create();
        Context::factory()->name('more_filler')->create();

        Content::factory()
            ->withUser($user)
            ->withVersion(ContentVersion::factory()->title('my beautiful content'))
            ->create();

        $this->browse(
            fn(Browser $browser) => $browser
                ->loginAs($user->email)
                ->assertAuthenticated()
                ->visit('/content/mine')
                ->with(
                    new ContentCard(),
                    fn(Browser $card) => $card
                        ->assertSeeIn('@title', 'my beautiful content')
                        ->click('@details-button'),
                )
                ->clickLink('Roles')
            // Dusk doesn't support selecting the actual visual text
                ->select('[name="context"]', $context->id)
                ->press('Add')
                ->assertSee('The context was added to the content.')
                ->assertSeeIn('.content-contexts > tbody > tr:first-child > td:nth-child(1)', 'desired'),
        );
    }

    public function testContentCreatedInLtiContextInheritsPlatformRoles(): void
    {
        $platform = LtiPlatform::factory()
            ->withContext(Context::factory()->name('ndla_people'), ContentRole::Editor)
            ->create();
        LtiTool::factory()
            ->withName('My tool')
            ->launchUrl('https://hub-test.edlib.test/lti/samples/deep-link')
            ->withCredentials($platform->getOauth1Credentials())
            ->create();
        $user = User::factory()
            ->withEmail('foo@example.com')
            ->create();

        $this->browse(
            fn(Browser $browser) => $browser
                ->loginAs($user->email)
                ->assertAuthenticated()
                ->visit('/lti/playground')
                ->type('launch_url', 'https://hub-test.edlib.test/lti/dl')
                ->type('key', $platform->key)
                ->type('secret', $platform->secret)
                ->type('parameters', 'lis_person_contact_email_primary=foo@example.com')
                ->press('Launch')
                ->withinFrame(
                    'iframe',
                    fn(Browser $frame) => $frame
                        ->clickLink('Create')
                        ->withinFrame(
                            'iframe',
                            fn(Browser $tool) => $tool
                                ->type('payload', <<<EOJSON
                    {
                        "@context": "http://purl.imsglobal.org/ctx/lti/v1/ContentItem",
                        "@graph": [
                            {
                                "@type": "LtiLinkItem",
                                "mediaType": "application/vnd.ims.lti.v1.ltilink",
                                "url": "https://hub-test.edlib.test/lti/samples/presentation",
                                "title": "My new content"
                            }
                        ]
                    }
                    EOJSON)
                                ->press('Send'),
                        ),
                )
                ->withinFrame(
                    'iframe[name="launch_frame"]',
                    fn(Browser $launch) => $launch
                        ->waitForText('My new content'),
                )
                ->visit('/content/mine')
                ->with(
                    new ContentCard(),
                    fn(Browser $card) => $card
                        ->assertSeeIn('@title', 'My new content')
                        ->click('@details-button'),
                )
                ->clickLink('Roles')
                ->assertSeeIn('.content-contexts > tbody > tr:first-child > td:nth-child(1)', 'ndla_people'),
        );
    }

    public function testCanEditContentWhenGivenEditorRoleViaContext(): void
    {
        $context = Context::factory()
            ->name('my_context')
            ->create();

        Content::factory()
            ->shared()
            ->withContext($context)
            ->withVersion(
                ContentVersion::factory()
                    ->title('The content with context')
                    ->published(),
            )
            ->create();

        $platform = LtiPlatform::factory()
            ->withContext($context, ContentRole::Editor)
            ->create();

        $user = User::factory()
            ->withEmail('person@example.com')
            ->create();

        $this->browse(
            fn(Browser $browser) => $browser
                ->loginAs($user->email)
                ->assertAuthenticated()
                ->visit('/lti/playground')
                ->type('launch_url', 'https://hub-test.edlib.test/lti/dl')
                ->type('key', $platform->key)
                ->type('secret', $platform->secret)
                ->type(
                    'parameters',
                    'content_item_return_url=about:blank' .
                    '&lti_message_type=ContentItemSelectionRequest' .
                    '&lis_person_contact_email_primary=person@example.com',
                )
                ->press('Launch')
                ->withinFrame(
                    'iframe',
                    fn(Browser $edlib) => $edlib
                        ->with(
                            new ContentCard(),
                            fn(Browser $card) => $card
                                ->assertPresent('@edit-button'),
                        ),
                ),
        );
    }

    public function testCannotEditContentInContextsOneIsNotPartOf(): void
    {
        Content::factory()
            ->shared()
            ->withContext(
                Context::factory()->name('someone_elses_context'),
            )
            ->withVersion(
                ContentVersion::factory()
                    ->title('The content to not edit')
                    ->published(),
            )
            ->create();

        $platform = LtiPlatform::factory()
            ->withContext(Context::factory()->name('my_context'), ContentRole::Editor)
            ->create();

        $user = User::factory()
            ->withEmail('person@example.com')
            ->create();

        $this->browse(
            fn(Browser $browser) => $browser
                ->loginAs($user->email)
                ->assertAuthenticated()
                ->visit('/lti/playground')
                ->type('launch_url', 'https://hub-test.edlib.test/lti/dl')
                ->type('key', $platform->key)
                ->type('secret', $platform->secret)
                ->type(
                    'parameters',
                    'content_item_return_url=about:blank' .
                    '&lti_message_type=ContentItemSelectionRequest' .
                    '&lis_person_contact_email_primary=person@example.com',
                )
                ->press('Launch')
                ->withinFrame(
                    'iframe',
                    fn(Browser $edlib) => $edlib
                        ->with(
                            new ContentCard(),
                            fn(Browser $card) => $card
                                ->press('@action-menu-toggle')
                                ->assertNotPresent('@edit-link'),
                        ),
                ),
        );
    }

    /**
     * Content should only inherit context when created, not when edited.
     * Toggling the shared status counts as an edit, so we do this to check that
     * the undesired behaviour doesn't happen anymore.
     */
    public function testExistingContentSavedDuringLtiLaunchContextDoesNotInheritContexts(): void
    {
        $user = User::factory()
            ->withEmail('person@example.com')
            ->create();

        $platform = LtiPlatform::factory()
            ->withContext(Context::factory()->name('should_not_exist'), ContentRole::Editor)
            ->create();

        Content::factory()
            ->withContext(Context::factory()->name('existing_context'))
            ->withVersion(ContentVersion::factory()->published())
            ->shared(false)
            ->withUser($user)
            ->createQuietly();

        RebuildContentIndex::dispatchSync();

        $this->browse(
            fn(Browser $browser) => $browser
                ->loginAs($user->email)
                ->assertAuthenticated()
                ->visit('/lti/playground')
                ->type('launch_url', 'https://hub-test.edlib.test/lti/dl')
                ->type('key', $platform->key)
                ->type('secret', $platform->secret)
                ->type(
                    'parameters',
                    'content_item_return_url=about:blank' .
                    '&lti_message_type=ContentItemSelectionRequest' .
                    '&lis_person_contact_email_primary=person@example.com',
                )
                ->press('Launch')
                ->withinFrame('iframe', function (Browser $iframe) {
                    $iframe
                        ->clickLink('My content')
                        ->waitForText('1 content found')
                        ->with(
                            new ContentCard(),
                            fn(Browser $card) => $card
                                ->press('@action-menu-toggle')
                                ->clickLink('Details'),
                        )
                        ->waitForText('Share the content with others')
                        ->scrollIntoView('#shared-toggle')
                        ->assertNotChecked('#shared-toggle')
                        // The horrible Bootstrap toggles don't work with Dusk's
                        // click/press/check methods.
                        ->script('document.querySelector("#shared-toggle").click()');

                    $iframe
                        ->clickLink('Content')
                        ->scrollIntoView('#shared-toggle')
                        ->assertChecked('#shared-toggle')
                        ->clickLink('Roles')
                        ->waitForText('Contexts')
                        ->assertSeeIn('.content-contexts', 'existing_context')
                        ->assertDontSeeIn('.content-contexts', 'should_not_exist');
                }),
        );
    }

    public function testDoesNotListToolsOnCreateIfOneTool(): void
    {
        $platform = LtiPlatform::factory()->create();
        LtiTool::factory()
            ->withName('Only Tool')
            ->slug('only-tool')
            ->launchUrl('https://hub-test.edlib.test/lti/samples/deep-link')
            ->withCredentials($platform->getOauth1Credentials())
            ->create();
        $user = User::factory()->admin()->create();

        $this->browse(
            fn(Browser $browser) => $browser
                ->loginAs($user->email)
                ->assertAuthenticated()
                ->visit('/')
                ->clickLink('Create')
                ->assertUrlIs('https://hub-test.edlib.test/content/create/only-tool')
                ->assertPresent('.lti-launch'),
        );
    }

    public function testDoesNotListToolsOnCreateIfOneToolWithOnlyAdminExtra(): void
    {
        $platform = LtiPlatform::factory()->create();
        LtiTool::factory()
            ->withName('Only Tool')
            ->slug('only-tool')
            ->launchUrl('https://hub-test.edlib.test/lti/samples/deep-link')
            ->withCredentials($platform->getOauth1Credentials())
            ->extra(
                LtiToolExtra::factory()
                    ->state([
                        'name' => 'Tool Admin',
                        'admin' => true,
                    ]),
            )
            ->create();
        $user = User::factory()->admin()->create();

        $this->browse(
            fn(Browser $browser) => $browser
                ->loginAs($user->email)
                ->assertAuthenticated()
                ->visit('/')
                ->clickLink('Create')
                ->assertUrlIs('https://hub-test.edlib.test/content/create/only-tool')
                ->assertPresent('.lti-launch'),
        );
    }

    public function testListToolsOnCreateIfMoreThanOneTool(): void
    {
        $platform = LtiPlatform::factory()->create();
        LtiTool::factory()
            ->withName('First Tool')
            ->withCredentials($platform->getOauth1Credentials())
            ->create();
        LtiTool::factory()
            ->withName('Second Tool')
            ->withCredentials($platform->getOauth1Credentials())
            ->create();
        $user = User::factory()->admin()->create();

        $this->browse(
            fn(Browser $browser) => $browser
                ->loginAs($user->email)
                ->assertAuthenticated()
                ->visit('/')
                ->clickLink('Create')
                ->assertSee('First Tool')
                ->assertSee('Second Tool'),
        );
    }

    public function testListToolsOnCreateIfOneToolWithExtra(): void
    {
        $platform = LtiPlatform::factory()->create();
        LtiTool::factory()
            ->withName('Only Tool')
            ->withCredentials($platform->getOauth1Credentials())
            ->extra(
                LtiToolExtra::factory()
                    ->state([
                        'name' => 'Tool Extra',
                        'admin' => false,
                    ]),
            )
            ->create();
        $user = User::factory()->admin()->create();

        $this->browse(
            fn(Browser $browser) => $browser
                ->loginAs($user->email)
                ->assertAuthenticated()
                ->clickLink('Create')
                ->assertSee('Only Tool')
                ->assertSee('Tool Extra'),
        );
    }

    public function testCanListContentWhenSearchAndDatabaseMismatch(): void
    {
        $content = Content::factory()
            ->withVersion(
                ContentVersion::factory()
                    ->title('This is my soon-to-be-gone content')
                    ->published(),
            )
            ->shared()
            ->create();

        RebuildContentIndex::dispatchSync();

        $this->browse(
            fn(Browser $browser) => $browser
                ->visit('https://hub-test.edlib.test/content')
                ->assertSeeIn('.content-card', 'This is my soon-to-be-gone content'),
        );

        $content->deleteQuietly();

        $this->browse(
            fn(Browser $browser) => $browser
                ->visit('https://hub-test.edlib.test/content')
                ->assertTitleContains('Explore')
                ->assertDontSee('.content-card'),
        );
    }

    public function testUseButtonDoesNotDisplayInDeepLinkingContextWithoutReturnUrl(): void
    {
        $user = User::factory()->create();
        Content::factory()
            ->withVersion(
                ContentVersion::factory()
                    ->title('My content')
                    ->published(),
            )
            ->shared()
            ->withUser($user)
            ->create();
        $platform = LtiPlatform::factory()->create();

        $this->browse(
            fn(Browser $browser) => $browser
                ->loginAs($user->email)
                ->visit('/lti/playground')
                ->type('launch_url', 'https://hub-test.edlib.test/lti/dl')
                ->type('key', $platform->key)
                ->type('secret', $platform->secret)
                ->type(
                    'parameters',
                    'lti_message_type=ContentItemSelectionRequest' .
                    "&lis_person_contact_email_primary={$user->email}",
                )
                ->press('Launch')
                ->withinFrame(
                    'iframe',
                    fn(Browser $iframe) => $iframe
                        ->with(
                            new ContentCard(),
                            fn(Browser $card) => $card
                                ->assertSeeIn('@title', 'My content')
                                ->assertDontSee('Use content'),
                        ),
                ),
        );
    }

    public function testCanPublishContent(): void
    {
        $owner = User::factory()->create();

        $content = Content::factory()
            ->withVersion(
                ContentVersion::factory()
                    ->title('To be published')
                    ->unpublished(),
            )
            ->withUser($owner)
            ->create();

        $this->browse(
            fn(Browser $browser) => $browser
                ->loginAs($owner->email)
                ->assertAuthenticated()
                ->visit('https://hub-test.edlib.test/content/' . $content->id . '/version/' . $content->latestVersion?->id)
                ->assertTitleContains('To be published')
                ->assertSee('You are viewing an unpublished draft version.')
                ->press('Publish')
                ->assertTitleContains('To be published')
                ->assertDontSee('You are viewing an unpublished draft version.'),
        );
    }

    public function testEmbedUsesLocaleFromQuery(): void
    {
        $content = Content::factory()
            ->withPublishedVersion()
            ->shared()
            ->create();

        $this->browse(
            fn(Browser $browser) => $browser
                ->visit("https://hub-test.edlib.test/content/$content->id/embed?locale=nb-NO")
                ->assertAttribute('', 'lang', 'nb-NO'),
        );
    }
}
