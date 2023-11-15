<?php

declare(strict_types=1);

namespace Tests\Browser;

use App\Models\Content;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

final class ContentTest extends DuskTestCase
{
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
