<?php

declare(strict_types=1);

namespace Tests\Integration\Http\Controllers\Admin;

use App\Content;
use App\ContentVersion;
use App\H5PContent;
use App\H5PLibrary;
use Illuminate\Auth\GenericUser;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Collection;
use Illuminate\View\View;
use Tests\TestCase;

class VersioningControllerTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    public function testIndex_versionedContent(): void
    {
        $user = new GenericUser([
            'roles' => ['superadmin'],
            'name' => 'Just Testing',
        ]);
        $library = H5PLibrary::factory()->create();
        $parent = H5PContent::factory()->create([
            'version_id' => $this->faker->unique()->uuid,
            'library_id' => '',
        ]);
        $child = H5PContent::factory()->create([
            'version_id' => $this->faker->unique()->uuid,
            'library_id' => $library->id,
        ]);
        $parentVersion = ContentVersion::factory()->create([
            'id' => $parent->version_id,
            'content_id' => $parent->id,
            'content_type' => Content::TYPE_H5P,
            'version_purpose' => ContentVersion::PURPOSE_CREATE,
        ]);
        $childVersion = ContentVersion::factory()->create([
            'id' => $child->version_id,
            'content_id' => $child->id,
            'content_type' => Content::TYPE_H5P,
            'parent_id' => $parentVersion->id,
            'version_purpose' => ContentVersion::PURPOSE_UPDATE,
        ]);

        $result = $this->withSession(['user' => $user])
            ->get(route('admin.support.versioning', ['contentId' => $parent->id]))
            ->assertOk()
            ->original;

        $this->assertInstanceOf(View::class, $result);
        $data = $result->getData();
        $this->assertEquals($data['contentId'], $parent->id);
        $this->assertInstanceOf(Collection::class, $data['versionData']);
        $this->assertArrayHasKey($parent->id, $data['versionData']);
        $this->assertArrayHasKey($child->id, $data['versionData']);
        $this->assertTrue($data['isContentVersioned']);

        $parentData = $data['versionData'][$parent->id];
        $this->assertSame($parentVersion->id, $parentData['version']['id']);
        $this->assertEquals($parent->id, $parentData['version']['content_id']);
        $this->assertSame($parent->title, $parentData['content']['title']);
        $this->assertSame('', $parentData['content']['library']);

        $childData = $data['versionData'][$child->id];
        $this->assertSame($childVersion->id, $childData['version']['id']);
        $this->assertEquals($child->id, $childData['version']['content_id']);
        $this->assertSame($child->title, $childData['content']['title']);
        $this->assertSame($library->getLibraryString(true), $childData['content']['library']);
    }

    public function testIndex_unversionedContent(): void
    {
        $user = new GenericUser([
            'roles' => ['superadmin'],
            'name' => 'Just Testing',
        ]);
        $library = H5PLibrary::factory()->create();
        $content = H5PContent::factory()->create([
            'library_id' => $library->id,
        ]);

        $result = $this->withSession(['user' => $user])
            ->get(route('admin.support.versioning', ['contentId' => $content->id]))
            ->assertOk()
            ->original;

        $this->assertInstanceOf(View::class, $result);

        $data = $result->getData();
        $this->assertEquals($data['contentId'], $content->id);
        $this->assertNull($data['versionData']);
        $this->assertFalse($data['isContentVersioned']);
    }
}
