<?php

declare(strict_types=1);

namespace Tests\Browser;

use App\Models\Content;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

final class ContentTest extends DuskTestCase
{
    use DatabaseMigrations;

    public function testPreviewsContent(): void
    {
        $content = Content::factory()
            ->withPublishedVersion()
            ->create()
            ->fresh(); // FIXME: why won't this work without?

        $this->browse(function (Browser $browser) use ($content) {
            $browser->visit('/content/'.$content->id)
                ->assertTitleContains($content->latestVersion->resource->title)
                ->assertPresent('iframe');
        });
    }
}
