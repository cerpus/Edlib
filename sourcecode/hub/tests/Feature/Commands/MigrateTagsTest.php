<?php

declare(strict_types=1);

namespace Tests\Feature\Commands;

use App\Models\Content;
use App\Models\ContentVersion;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MigrateTagsTest extends TestCase
{
    use RefreshDatabase;

    public function testMigratesTheTags(): void
    {
        $content = Content::factory()
            ->tag('edlib2_id:5e60a4f8-dbae-4a5f-8f8f-bd8e5befd13d')
            ->tag('edlib2_usage_id:da04f2de-256f-4a0b-b766-681629c19520')
            ->withVersion(
                ContentVersion::factory()
                    ->title('My content')
                    ->displayedContentType(null)
                    ->withTag('h5p:H5P.SomeLibrary'),
            )
            ->create();

        $this->artisan('edlib:migrate-tags');

        $content->refresh();

        $this->assertSame('5e60a4f8-dbae-4a5f-8f8f-bd8e5befd13d', $content->edlib2_id);
        $this->assertSame('da04f2de-256f-4a0b-b766-681629c19520', $content->edlib2Usages()->firstOrFail()->edlib2_usage_id);
        $this->assertSame('H5P.SomeLibrary', $content->latestVersion?->getRawDisplayedContentType());
    }
}
