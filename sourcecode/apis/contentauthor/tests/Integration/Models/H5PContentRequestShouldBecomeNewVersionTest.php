<?php

namespace Tests\Integration\Models;

use App\H5PContent;
use App\H5PContentsMetadata;
use App\H5PLibrary;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\Request;
use Tests\TestCase;

/**
 * @covers \App\H5PContent::requestShouldBecomeNewVersion
 */
class H5PContentRequestShouldBecomeNewVersionTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    private H5PContent|Model $contentDraft;
    private H5PContent|Model $content;

    public function setUp(): void
    {
        parent::setUp();

        /** @var H5PLibrary|Model $library42 */
        $library42 = H5PLibrary::factory()->create([
            'name' => 'H5P.UnitTest',
            'major_version' => 4,
            'minor_version' => 2,
            'patch_version' => 0,
        ]);
        H5PLibrary::factory()->create([
            'name' => 'H5P.UnitTest',
            'major_version' => 4,
            'minor_version' => 3,
            'patch_version' => 0,
        ]);

        $this->contentDraft = H5PContent::factory()->create([
            'title' => $this->faker->words(3, true),
            'is_draft' => true,
            'library_id' => $library42->id,
        ]);

        H5PContentsMetadata::factory()->create([
            'content_id' => $this->contentDraft->id,
        ]);

        $this->content = H5PContent::factory()->create([
            'title' => $this->faker->words(3, true),
            'is_draft' => false,
            'library_id' => $library42->id,
        ]);

        H5PContentsMetadata::factory()->create([
            'content_id' => $this->content->id,
        ]);
    }

    public function testUpdateDraft(): void
    {
        $this->assertFalse($this->contentDraft->requestShouldBecomeNewVersion(new Request([], [
            'title' => 'Title has changed',
        ])));
    }

    public function testUpdate(): void
    {
        $this->assertTrue($this->content->requestShouldBecomeNewVersion(new Request([], [
            'title' => 'Title has changed',
            'parameters' => json_encode(['params' => []]),
            'license' => $this->content->getContentLicense(),
        ])));
    }

    public function testSaveDraftAsDraft(): void
    {
        $this->assertFalse($this->contentDraft->requestShouldBecomeNewVersion(new Request([], ['isDraft' => "1"])));
        $this->assertFalse($this->contentDraft->requestShouldBecomeNewVersion(new Request([], ['isDraft' => true])));
    }

    public function testSaveAsDraft(): void
    {
        $this->assertTrue($this->content->requestShouldBecomeNewVersion(new Request([], ['isDraft' => "1"])));
        $this->assertTrue($this->content->requestShouldBecomeNewVersion(new Request([], ['isDraft' => true])));
    }

    public function testDraftNewerLibraryVersion(): void
    {
        $this->assertFalse($this->contentDraft->requestShouldBecomeNewVersion(new Request([], [
            'title' => $this->contentDraft->title,
            'parameters' => json_encode(['params' => []]),
            'license' => $this->contentDraft->getContentLicense(),
            'library' => 'H5P.UnitTest 4.3',
        ])));
    }

    public function testNewerLibraryVersion(): void
    {
        $this->assertTrue($this->content->requestShouldBecomeNewVersion(new Request([], [
            'title' => $this->content->title,
            'parameters' => json_encode(['params' => []]),
            'license' => $this->content->getContentLicense(),
            'library' => 'H5P.UnitTest 4.3',
        ])));
    }

    public function testDraftNewLanguage(): void
    {
        $this->assertTrue($this->contentDraft->requestShouldBecomeNewVersion(new Request([], [
            'title' => $this->contentDraft->title,
            'parameters' => json_encode(['params' => []]),
            'license' => $this->contentDraft->getContentLicense(),
            'library' => 'H5P.UnitTest 4.2',
            'isNewLanguageVariant' => '1',
        ])));
    }

    public function testNewLanguage(): void
    {
        $this->assertTrue($this->content->requestShouldBecomeNewVersion(new Request([], [
            'title' => $this->content->title,
            'parameters' => json_encode(['params' => []]),
            'license' => $this->content->getContentLicense(),
            'library' => 'H5P.UnitTest 4.2',
            'isNewLanguageVariant' => '1',
        ])));
    }

    public function testDraftNoChange(): void
    {
        $this->assertFalse($this->contentDraft->requestShouldBecomeNewVersion(new Request([], [
            'title' => $this->contentDraft->title,
            'parameters' => json_encode(['params' => []]),
            'license' => $this->contentDraft->getContentLicense(),
            'library' => 'H5P.UnitTest 4.2',
        ])));
    }

    public function testNoChange(): void
    {
        $this->assertFalse($this->content->requestShouldBecomeNewVersion(new Request([], [
            'title' => $this->content->title,
            'parameters' => json_encode(['params' => []]),
            'license' => $this->content->getContentLicense(),
            'library' => 'H5P.UnitTest 4.2',
        ])));
    }
}
