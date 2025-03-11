<?php

declare(strict_types=1);

namespace Tests\Browser;

use App\Jobs\RebuildContentIndex;
use App\Models\Content;
use App\Models\ContentVersion;
use App\Models\Tag;
use Illuminate\Support\Carbon;
use Laravel\Dusk\Browser;
use Tests\Browser\Components\ContentCard;
use Tests\Browser\Components\FilterForm;
use Tests\DuskTestCase;

final class FilterTest extends DuskTestCase
{
    public function testCanToggleFilter(): void
    {
        $this->browse(
            fn(Browser $browser) => $browser
                ->visit('/content')
                ->with(
                    new FilterForm(),
                    fn($filter) => $filter
                        ->assertCollapsed()
                        ->expand()
                        ->assertExpanded()
                        ->collapse()
                        ->assertCollapsed(),
                ),
        );
    }

    public function testCanSearch(): void
    {
        $content = Content::factory()
            ->withVersion(
                ContentVersion::factory()
                    ->published()
                    ->state([
                        'title' => 'Find me',
                    ]),
            )
            ->shared()
            ->create();
        Content::factory()
            ->withVersion(
                ContentVersion::factory()
                    ->published()
                    ->state([
                        'title' => 'Not in result',
                    ]),
            )
            ->shared()
            ->create();

        $this->browse(
            fn(Browser $browser) => $browser
                ->visit('/content')
                ->assertSee('2 contents found')
                ->with(
                    new FilterForm(),
                    fn($filter) => $filter
                        ->typeSearchText('find'),
                )
                ->waitForEvent('htmx:afterSwap')
                ->assertSee('1 content found')
                ->with(
                    new ContentCard(),
                    fn($card) => $card
                        ->assertSeeIn('@title', $content->getTitle()),
                ),
        );
    }

    public function testCanFilterOnContentType(): void
    {
        Content::factory()
            ->withVersion(
                ContentVersion::factory()
                    ->published()
                    ->withTag(Tag::factory()->asH5PContentType('magiccontent')),
            )
            ->shared()
            ->createQuietly();

        $content = Content::factory()
            ->withVersion(
                ContentVersion::factory()
                    ->published()
                    ->withTag(Tag::factory()->asH5PContentType('techtype'))
                    ->state([
                        'title' => 'Find me',
                    ]),
            )
            ->shared()
            ->createQuietly();

        // We must re-sync to include the tags in Meilisearch data
        RebuildContentIndex::dispatchSync();

        $this->browse(
            fn(Browser $browser) => $browser
                ->visit('/content')
                ->assertSee('2 contents found')
                ->with(
                    new FilterForm(),
                    fn($filter) => $filter
                        ->expand()
                        ->withContentTypeFilter(
                            fn($typeFilter) => $typeFilter
                                ->selectOption('h5p.techtype'),
                        ),
                )
                ->waitForEvent('htmx:afterSwap')
                ->assertSee('1 content found')
                ->with(
                    new ContentCard(),
                    fn($card) => $card
                        ->assertSeeIn('@title', $content->getTitle()),
                )
                ->with(
                    new FilterForm(),
                    fn($filter) => $filter
                        ->assertExpanded()
                        ->withContentTypeFilter(
                            fn($typeFilter) => $typeFilter
                                ->assertOptionSelected('h5p.techtype')
                                ->selectOption('h5p.magiccontent'),
                        ),
                )
                ->waitForEvent('htmx:afterSwap')
                ->assertSee('2 contents found')
                ->with(
                    new FilterForm(),
                    fn($filter) => $filter
                        ->assertExpanded()
                        ->withContentTypeFilter(
                            fn($typeFilter) => $typeFilter
                                ->assertOptionSelected('h5p.techtype')
                                ->assertOptionSelected('h5p.magiccontent')
                                ->assertNoOptionsAvailable(),
                        ),
                ),
        );
    }

    public function testCanRemoveSelectedContentTypeOption(): void
    {
        Content::factory()
            ->withVersion(
                ContentVersion::factory()
                    ->published()
                    ->withTag(Tag::factory()->asH5PContentType('magiccontent')),
            )
            ->shared()
            ->createQuietly();

        Content::factory()
            ->withVersion(
                ContentVersion::factory()
                    ->published()
                    ->withTag(Tag::factory()->asH5PContentType('techtype'))
                    ->state([
                        'title' => 'Find me',
                    ]),
            )
            ->shared()
            ->createQuietly();

        // We must re-sync to include the tags in Meilisearch data
        RebuildContentIndex::dispatchSync();

        $this->browse(
            fn(Browser $browser) => $browser
                ->visit('/content')
                ->assertSee('2 contents found')
                ->with(
                    new FilterForm(),
                    fn($filter) => $filter
                        ->expand()
                        ->withContentTypeFilter(
                            fn($typeFilter) => $typeFilter
                                ->selectOption('h5p.techtype'),
                        ),
                )
                ->waitForEvent('htmx:afterSwap')
                ->assertSee('1 content found')
                ->with(
                    new FilterForm(),
                    fn($filter) => $filter
                        ->withContentTypeFilter(
                            fn($typeFilter) => $typeFilter
                                ->assertOptionSelected('h5p.techtype')
                                ->removeSelectedOption('h5p.techtype'),
                        ),
                )
                ->waitForEvent('htmx:afterSwap')
                ->assertSee('2 contents found')
                ->with(
                    new FilterForm(),
                    fn($filter) => $filter
                        ->withContentTypeFilter(
                            fn($typeFilter) => $typeFilter
                                ->assertNoOptionsSelected(),
                        ),
                ),
        );
    }

    public function testCanFilterContentTypeOptions(): void
    {
        Content::factory()
            ->withVersion(
                ContentVersion::factory()
                    ->published()
                    ->withTag(Tag::factory()->asH5PContentType('magiccontent')),
            )
            ->shared()
            ->createQuietly();

        Content::factory()
            ->withVersion(
                ContentVersion::factory()
                    ->published()
                    ->withTag(Tag::factory()->asH5PContentType('techtype'))
                    ->state([
                        'title' => 'Find me',
                    ]),
            )
            ->shared()
            ->createQuietly();

        // We must re-sync to include the tags in Meilisearch data
        RebuildContentIndex::dispatchSync();

        $this->browse(
            fn(Browser $browser) => $browser
                ->visit('/content')
                ->with(
                    new FilterForm(),
                    fn($filter) => $filter
                        ->expand()
                        ->withContentTypeFilter(
                            fn($typeFilter) => $typeFilter
                                ->assertHasOption('h5p.techtype')
                                ->assertHasOption('h5p.magiccontent')
                                ->typeOptionsFilter('tech')
                                ->assertHasOption('h5p.techtype')
                                ->assertNotHasOption('h5p.magiccontent'),
                        ),
                ),
        );
    }

    public function testCanFilterOnLanguage(): void
    {
        $content = Content::factory()
            ->withVersion(
                ContentVersion::factory()
                    ->published()
                    ->state([
                        'language_iso_639_3' => 'nob',
                        'title' => 'Find me',
                    ]),
            )
            ->shared()
            ->create();

        Content::factory()
            ->withVersion(
                ContentVersion::factory()
                    ->published()
                    ->state([
                        'language_iso_639_3' => 'eng',
                    ]),
            )
            ->shared()
            ->create();

        $this->browse(
            fn(Browser $browser) => $browser
                ->visit('/content')
                ->assertSee('2 contents found')
                ->with(
                    new FilterForm(),
                    fn($filter) => $filter
                        ->expand()
                        ->withLanguageFilter(
                            fn($langFilter) => $langFilter
                                ->assertOptionSelected('')
                                ->assertHasOption('nob')
                                ->selectOption('nob'),
                        ),
                )
                ->waitForEvent('htmx:afterSwap')
                ->assertSee('1 content found')
                ->with(
                    new ContentCard(),
                    fn($card) => $card
                        ->assertSeeIn('@title', $content->getTitle()),
                )
                ->with(
                    new FilterForm(),
                    fn($filter) => $filter
                        ->assertExpanded()
                        ->withLanguageFilter(
                            fn($langFilter) => $langFilter
                                ->assertOptionSelected('nob'),
                        ),
                ),
        );
    }

    public function testCanFilterLanguageOptions(): void
    {
        Content::factory()
            ->withVersion(
                ContentVersion::factory()
                    ->published()
                    ->state([
                        'language_iso_639_3' => 'nob',
                        'title' => 'Find me',
                    ]),
            )
            ->shared()
            ->create();

        Content::factory()
            ->withVersion(
                ContentVersion::factory()
                    ->published()
                    ->state([
                        'language_iso_639_3' => 'eng',
                    ]),
            )
            ->shared()
            ->create();

        $this->browse(
            fn(Browser $browser) => $browser
                ->visit('/content')
                ->with(
                    new FilterForm(),
                    fn($filter) => $filter
                        ->expand()
                        ->withLanguageFilter(
                            fn($choice) => $choice
                                ->assertHasOption('nob')
                                ->assertHasOption('eng')
                                ->typeOptionsFilter('nob')
                                ->assertHasOption('nob')
                                ->assertNotHasOption('eng'),
                        ),
                ),
        );
    }

    public function testCanSort(): void
    {
        $created = Content::factory()
            ->withVersion(
                ContentVersion::factory()
                    ->published()
                    ->state([
                        'created_at' => Carbon::now()->subDay(), // Date for updated sorting
                        'title' => 'First in created sorting',
                    ]),
            )
            ->shared()
            ->create([
                'created_at' => Carbon::now()->subDay(), // Date for created sorting
            ]);

        $edited = Content::factory()
            ->withVersion(
                ContentVersion::factory()
                    ->published()
                    ->state([
                        'created_at' => Carbon::now(), // Date for updated sorting
                        'title' => 'First in edited/updated sorting',
                    ]),
            )
            ->shared()
            ->create([
                'created_at' => Carbon::now()->subDays(2), // Date for created sorting
            ]);

        $this->browse(
            fn(Browser $browser) => $browser->visit('/content')
                ->assertSee('2 contents found')
                ->with(
                    new ContentCard(),
                    fn($card) => $card
                    // The first card in the listing
                        ->assertSeeIn('@title', $edited->getTitle()),
                )
                ->with(
                    new FilterForm(),
                    fn($filter) => $filter
                        ->expand()
                        ->withSortOrder(
                            fn($sort) => $sort
                                ->assertOptionSelected('updated')
                                ->selectOption('created'),
                        ),
                )
                ->waitForEvent('htmx:afterSwap')
                ->assertSee('2 contents found')
                ->with(
                    new ContentCard(),
                    fn($card) => $card
                    // The first card in the listing
                        ->assertSeeIn('@title', $created->getTitle()),
                )
                ->with(
                    new FilterForm(),
                    fn($filter) => $filter
                        ->assertExpanded()
                        ->withSortOrder(
                            fn($sort) => $sort
                                ->assertOptionSelected('created'),
                        ),
                ),
        );
    }

    public function testCanSeeActiveFilterCount(): void
    {
        Content::factory()
            ->withVersion(
                ContentVersion::factory()
                    ->published()
                    ->withTag(Tag::factory()->asH5PContentType('magiccontent'))
                    ->state([
                        'language_iso_639_3' => 'nob',
                        'title' => 'Norsk bokmål innhold',
                    ]),
            )
            ->shared()
            ->createQuietly();

        Content::factory()
            ->withVersion(
                ContentVersion::factory()
                    ->published()
                    ->withTag(Tag::factory()->asH5PContentType('techtype'))
                    ->state([
                        'language_iso_639_3' => 'eng',
                        'title' => 'English content',
                    ]),
            )
            ->shared()
            ->createQuietly();

        // We must re-sync to include the tags in Meilisearch data
        RebuildContentIndex::dispatchSync();

        $this->browse(
            fn(Browser $browser) => $browser
                ->visit('/content')
                ->assertSee('2 contents found')
                ->with(
                    new FilterForm(),
                    fn($filter) => $filter
                        ->assertActiveFilterCount(0)
                        ->expand()
                        ->withLanguageFilter(
                            fn($langFilter) => $langFilter
                                ->selectOption('nob'),
                        ),
                )
                ->waitForEvent('htmx:afterSwap')
                ->assertSee('1 content found')
                ->with(
                    new FilterForm(),
                    fn($filter) => $filter
                        ->assertActiveFilterCount(1)
                        ->assertExpanded()
                        ->withContentTypeFilter(
                            fn($typeFilter) => $typeFilter
                                ->selectOption('h5p.magiccontent'),
                        ),
                )
                ->waitForEvent('htmx:afterSwap')
                ->assertSee('1 content found')
                ->with(
                    new FilterForm(),
                    fn($filter) => $filter
                        ->assertActiveFilterCount(2)
                        ->assertExpanded()
                        ->withSortOrder(
                            fn($sort) => $sort
                                ->selectOption('created'),
                        ),
                )
                ->waitForEvent('htmx:afterSwap')
                ->assertSee('1 content found')
                ->with(
                    new FilterForm(),
                    fn($filter) => $filter
                        ->assertActiveFilterCount(2)
                        ->assertExpanded()
                        ->typeSearchText('innhold'),
                )
                ->waitForEvent('htmx:afterSwap')
                ->assertSee('1 content found')
                ->with(
                    new FilterForm(),
                    fn($filter) => $filter
                        ->assertActiveFilterCount(2)
                        ->collapse()
                        ->assertActiveFilterCount(2),
                ),
        );
    }

    public function testCanClearFilter(): void
    {
        Content::factory()
            ->withVersion(
                ContentVersion::factory()
                    ->published()
                    ->withTag(Tag::factory()->asH5PContentType('magiccontent'))
                    ->state([
                        'language_iso_639_3' => 'nob',
                        'title' => 'Norsk bokmål innhold',
                    ]),
            )
            ->shared()
            ->createQuietly();

        Content::factory()
            ->withVersion(
                ContentVersion::factory()
                    ->published()
                    ->withTag(Tag::factory()->asH5PContentType('techtype'))
                    ->state([
                        'language_iso_639_3' => 'eng',
                        'title' => 'English content',
                    ]),
            )
            ->shared()
            ->createQuietly();

        // We must re-sync to include the tags in Meilisearch data
        RebuildContentIndex::dispatchSync();

        $this->browse(
            fn(Browser $browser) => $browser
                ->visit('/content')
                ->assertSee('2 contents found')
                ->with(
                    new FilterForm(),
                    fn($filter) => $filter
                        ->assertActiveFilterCount(0)
                        ->expand()
                        ->assertClearFilterDisabled()
                        ->withLanguageFilter(
                            fn($langFilter) => $langFilter
                                ->selectOption('nob'),
                        )
                        ->withContentTypeFilter(
                            fn($typeFilter) => $typeFilter
                                ->selectOption('h5p.magiccontent'),
                        )
                        ->typeSearchText('innhold'),
                )
                ->waitForEvent('htmx:afterSwap')
                ->assertSee('1 content found')
                ->with(
                    new FilterForm(),
                    fn($filter) => $filter
                        ->assertActiveFilterCount(2)
                        ->assertClearFilterEnabled()
                        ->clearFilter(),
                )
                ->waitForLocation('/content')
                ->assertSee('2 contents found')
                ->with(
                    new FilterForm(),
                    fn($filter) => $filter
                        ->assertActiveFilterCount(0)
                        ->assertCollapsed()
                        ->expand()
                        ->assertClearFilterDisabled()
                        ->assertSearchTextIs('')
                        ->withLanguageFilter(
                            fn($langFilter) => $langFilter
                                ->assertOptionSelected(''),
                        )
                        ->withContentTypeFilter(
                            fn($typeFilter) => $typeFilter
                                ->assertNoOptionsSelected(),
                        ),
                ),
        );
    }

    public function testDoNotUseDeletedContentInFilterOptions(): void
    {
        Content::factory()
            ->withVersion(
                ContentVersion::factory()
                    ->published()
                    ->withTag(Tag::factory()->asH5PContentType('magiccontent'))
                    ->state([
                        'language_iso_639_3' => 'nob',
                    ]),
            )
            ->shared()
            ->create();

        Content::factory()
            ->withVersion(
                ContentVersion::factory()
                    ->published()
                    ->withTag(Tag::factory()->asH5PContentType('deletedcontent'))
                    ->state([
                        'language_iso_639_3' => 'swe',
                    ]),
            )
            ->shared()
            ->create([
                'deleted_at' => Carbon::now(),
            ]);

        // We must re-sync to include the tags in Meilisearch data
        RebuildContentIndex::dispatchSync();

        $this->browse(
            fn(Browser $browser) => $browser
                ->visit('/content')
                ->assertSee('1 content found')
                ->with(
                    new FilterForm(),
                    fn($filter) => $filter
                        ->expand()
                        ->withContentTypeFilter(
                            fn($typeFilter) => $typeFilter
                                ->assertNotHasOption('h5p.deletedcontent')
                                ->assertHasOption('h5p.magiccontent'),
                        )
                        ->withLanguageFilter(
                            fn($langFilter) => $langFilter
                                ->assertNotHasOption('swe')
                                ->assertHasOption('nob'),
                        ),
                ),
        );
    }

    public function testCanSeeResultCountInFilterOptions(): void
    {
        $tagMagic = Tag::factory()->asH5PContentType('magiccontent')->create();
        $tagTech = Tag::factory()->asH5PContentType('techtype')->create();

        Content::factory()
            ->withVersion(
                ContentVersion::factory()
                    ->published()
                    ->withTag($tagMagic)
                    ->state([
                        'language_iso_639_3' => 'nob',
                        'title' => 'Norwegian magic',
                    ]),
            )
            ->shared()
            ->createQuietly();

        Content::factory()
            ->withVersion(
                ContentVersion::factory()
                    ->published()
                    ->withTag($tagMagic)
                    ->state([
                        'language_iso_639_3' => 'swe',
                        'title' => 'Swedish magic',
                    ]),
            )
            ->shared()
            ->createQuietly();

        Content::factory()
            ->withVersion(
                ContentVersion::factory()
                    ->published()
                    ->withTag($tagTech)
                    ->state([
                        'language_iso_639_3' => 'nob',
                        'title' => 'Norwegian tech',
                    ]),
            )
            ->shared()
            ->createQuietly();

        Content::factory()
            ->withVersion(
                ContentVersion::factory()
                    ->published()
                    ->withTag($tagTech)
                    ->state([
                        'language_iso_639_3' => 'swe',
                        'title' => 'Swedish tech',
                    ]),
            )
            ->shared()
            ->createQuietly();

        // We must re-sync to include the tags in Meilisearch data
        RebuildContentIndex::dispatchSync();

        $this->browse(
            fn(Browser $browser) => $browser
                ->visit('/content')
                ->assertSee('4 contents found')
                ->with(
                    new FilterForm(),
                    fn($filter) => $filter
                        ->expand()
                        ->withLanguageFilter(
                            fn($langFilter) => $langFilter
                                ->assertSelectedOptionLabelContainsString('', '(4)')
                                ->assertOptionLabelContainsString('nob', '(2)')
                                ->assertOptionLabelContainsString('swe', '(2)'),
                        )
                        ->withContentTypeFilter(
                            fn($typeFilter) => $typeFilter
                                ->assertOptionLabelContainsString('h5p.magiccontent', '(2)')
                                ->assertOptionLabelContainsString('h5p.techtype', '(2)')
                                ->selectOption('h5p.techtype'),
                        ),
                )
                ->waitForEvent('htmx:afterSwap')
                ->assertSee('2 contents found')
                ->with(
                    new FilterForm(),
                    fn($filter) => $filter
                        ->withContentTypeFilter(
                            fn($typeFilter) => $typeFilter
                                ->assertOptionLabelContainsString('h5p.magiccontent', '(2)')
                                ->assertSelectedOptionLabelContainsString('h5p.techtype', '(2)'),
                        )
                        ->withLanguageFilter(
                            fn($langFilter) => $langFilter
                                ->assertSelectedOptionLabelContainsString('', '(2)')
                                ->assertOptionLabelContainsString('nob', '(1)')
                                ->assertOptionLabelContainsString('swe', '(1)')
                                ->selectOption('nob'),
                        ),
                )
                ->waitForEvent('htmx:afterSwap')
                ->assertSee('1 content found')
                ->with(
                    new FilterForm(),
                    fn($filter) => $filter
                        ->withLanguageFilter(
                            fn($langFilter) => $langFilter
                                ->assertOptionLabelContainsString('', '(2)')
                                ->assertSelectedOptionLabelContainsString('nob', '(1)')
                                ->assertOptionLabelContainsString('swe', '(1)'),
                        )
                        ->withContentTypeFilter(
                            fn($typeFilter) => $typeFilter
                                ->assertOptionLabelContainsString('h5p.magiccontent', '(1)')
                                ->assertSelectedOptionLabelContainsString('h5p.techtype', '(1)')
                                ->selectOption('h5p.magiccontent'),
                        ),
                )
                ->waitForEvent('htmx:afterSwap')
                ->assertSee('2 contents found')
                ->with(
                    new FilterForm(),
                    fn($filter) => $filter
                        ->withContentTypeFilter(
                            fn($typeFilter) => $typeFilter
                                ->assertSelectedOptionLabelContainsString('h5p.magiccontent', '(1)')
                                ->assertSelectedOptionLabelContainsString('h5p.techtype', '(1)'),
                        )
                        ->withLanguageFilter(
                            fn($langFilter) => $langFilter
                                ->assertOptionLabelContainsString('', '(4)')
                                ->assertSelectedOptionLabelContainsString('nob', '(2)')
                                ->assertOptionLabelContainsString('swe', '(2)'),
                        )
                        ->typeSearchText('tech'),
                )
                ->waitForEvent('htmx:afterSwap')
                ->assertSee('1 content found')
                ->with(
                    new FilterForm(),
                    fn($filter) => $filter
                        ->withContentTypeFilter(
                            fn($typeFilter) => $typeFilter
                                ->assertSelectedOptionLabelContainsString('h5p.magiccontent', '(0)')
                                ->assertSelectedOptionLabelContainsString('h5p.techtype', '(1)'),
                        )
                        ->withLanguageFilter(
                            fn($langFilter) => $langFilter
                                ->assertOptionLabelContainsString('', '(2)')
                                ->assertSelectedOptionLabelContainsString('nob', '(1)')
                                ->assertOptionLabelContainsString('swe', '(1)'),
                        ),
                ),
        );
    }
}
