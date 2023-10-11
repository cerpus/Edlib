<?php

declare(strict_types=1);

namespace Tests\Integration\Models;

use App\H5PContent;
use App\H5PContentsMetadata;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class H5PContentTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    public function test_getMetadataStructure_HasMetadata(): void
    {
        $content = H5PContent::factory()->create();

        H5PContentsMetadata::factory()->create([
            'content_id' => $content->id,
            'authors' => '[{"name":"Emily Quackfaster","role":"Author"}]',
            'source' => 'space',
            'year_from' => '2000',
            'year_to' => '2001',
            'license' => 'CC BY-NC',
            'license_version' => '4.0',
            'license_extras' => 'original',
            'author_comments' => 'No comment',
            'changes' => '[{"test":"Some comment"}]',
            'default_language' => 'nn',
        ]);

        $result = $content->getMetadataStructure();

        $this->assertSame($content->title, $result['title']);
        $this->assertSame('Emily Quackfaster', $result['authors'][0]->name);
        $this->assertSame('Author', $result['authors'][0]->role);
        $this->assertSame('Some comment', $result['changes'][0]->test);
        $this->assertSame('space', $result['source']);
        $this->assertSame('CC BY-NC', $result['license']);
        $this->assertSame('4.0', $result['licenseVersion']);
        $this->assertSame('original', $result['licenseExtras']);
        $this->assertSame('No comment', $result['authorComments']);
        $this->assertSame(2000, $result['yearFrom']);
        $this->assertSame(2001, $result['yearTo']);
        $this->assertSame('nn', $result['defaultLanguage']);
    }

    public function test_getMetadataStructure_NoMetadata(): void
    {
        $content = H5PContent::factory()->create();

        $result = $content->getMetadataStructure();

        $this->assertSame($content->title, $result['title']);
        $this->assertDatabaseMissing('h5p_contents_metadata', [
            'content_id' => $content->id,
        ]);
    }
}
