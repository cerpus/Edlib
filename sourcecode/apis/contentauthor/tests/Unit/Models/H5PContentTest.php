<?php

namespace Tests\Unit\Models;

use App\H5PContent;
use App\H5PContentsMetadata;
use App\H5PLibrary;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Tests\TestCase;
use Tests\Traits\WithFaker;

class H5PContentTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    public function setUp(): void
    {
        parent::setUp();
        $this->refreshDatabase();
    }

    /**
     * @covers \App\H5PContent::requestShouldBecomeNewVersion
    */
    public function testRequestShouldBecomeNewVersion(): void
    {
        /** @var H5PLibrary $library */
        $library = H5PLibrary::factory()->create([
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
        /** @var H5PContent $content */
        $content = H5PContent::factory()->create([
            'title' => $this->faker->words(3, true),
            'is_draft' => true,
            'library_id' => $library->id,
        ]);
        H5PContentsMetadata::factory()->create([
            'content_id' => $content->id,
        ]);

        // Resource is draft
        $this->assertFalse($content->requestShouldBecomeNewVersion(new Request()));

        // Resource is draft and a new language variant is requested
        $this->assertTrue($content->requestShouldBecomeNewVersion(new Request([], [
            'title' => $content->title,
            'parameters' => json_encode(['params' => []]),
            'license' => $content->getContentLicense(),
            'library' => 'H5P.UnitTest 4.2',
            'isNewLanguageVariant' => '1',
        ])));

        // Resource is draft and a new language variant is requested to be saved as draft
        $this->assertTrue($content->requestShouldBecomeNewVersion(new Request([], [
            'title' => $content->title,
            'parameters' => json_encode(['params' => []]),
            'license' => $content->getContentLicense(),
            'library' => 'H5P.UnitTest 4.2',
            'isNewLanguageVariant' => '1',
            'isDraft' => '1',
        ])));

        $content->is_draft = false;
        // Resource is not draft, but requested to be saved as a draft
        $this->assertTrue($content->requestShouldBecomeNewVersion(new Request([], ['isDraft' => "1"])));
        $this->assertTrue($content->requestShouldBecomeNewVersion(new Request([], ['isDraft' => true])));

        // Versioning is off
        config(['feature.versioning' => false]);
        $this->assertFalse($content->requestShouldBecomeNewVersion(new Request()));

        config(['feature.versioning' => true]);
        // Title has changed
        $this->assertTrue($content->requestShouldBecomeNewVersion(new Request([], [
            'title' => 'Title has changed',
            'parameters' => json_encode(['params' => []]),
            'license' => $content->getContentLicense(),
        ])));

        // No change to library
        $this->assertFalse($content->requestShouldBecomeNewVersion(new Request([], [
            'title' => $content->title,
            'parameters' => json_encode(['params' => []]),
            'license' => $content->getContentLicense(),
            'library' => 'H5P.UnitTest 4.2',
        ])));

        // Newer version of library
        $this->assertTrue($content->requestShouldBecomeNewVersion(new Request([], [
            'title' => $content->title,
            'parameters' => json_encode(['params' => []]),
            'license' => $content->getContentLicense(),
            'library' => 'H5P.UnitTest 4.3',
        ])));

        // New language version is requested to be saved
        $this->assertTrue($content->requestShouldBecomeNewVersion(new Request([], [
            'title' => $content->title,
            'parameters' => json_encode(['params' => []]),
            'license' => $content->getContentLicense(),
            'library' => 'H5P.UnitTest 4.2',
            'isNewLanguageVariant' => '1',
        ])));

        // Nothing changed
        $this->assertFalse($content->requestShouldBecomeNewVersion(new Request([], [
            'title' => $content->title,
            'parameters' => json_encode(['params' => []]),
            'license' => $content->getContentLicense(),
            'library' => 'H5P.UnitTest 4.2',
            'isNewLanguageVariant' => '0',
        ])));
    }
}

