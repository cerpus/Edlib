<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Content;
use App\Models\ContentVersion;
use Tests\TestCase;

final class ContentTest extends TestCase
{
    public function testCannotPreviewUnpublishedContent(): void
    {
        $content = Content::factory()
            ->has(ContentVersion::factory()->unpublished(), 'versions')
            ->create();

        $this->get("/content/{$content->id}")
            ->assertForbidden();
    }
}
