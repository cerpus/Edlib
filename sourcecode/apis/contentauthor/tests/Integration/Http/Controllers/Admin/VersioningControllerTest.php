<?php

declare(strict_types=1);

namespace Tests\Integration\Http\Controllers\Admin;

use App\H5PContent;
use App\H5PLibrary;
use App\Libraries\Versioning\VersionableObject;
use App\QuestionSet;
use Illuminate\Auth\GenericUser;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
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
            'library_id' => $library->id,
            'version_purpose' => VersionableObject::PURPOSE_CREATE,
        ]);
        $child = H5PContent::factory()->create([
            'library_id' => $library->id,
            'parent_id' => $parent->id,
            'version_purpose' => VersionableObject::PURPOSE_UPDATE,
        ]);

        $result = $this->withSession(['user' => $user])
            ->get(route('admin.support.versioning', ['contentId' => $parent->id]))
            ->assertOk()
            ->original;

        $this->assertInstanceOf(View::class, $result);
        $data = $result->getData();
        $this->assertEquals($data['contentId'], $parent->id);
        $this->assertArrayHasKey($parent->id, $data['versionData']);
        $this->assertArrayHasKey($child->id, $data['versionData']);
        $this->assertTrue($data['isContentVersioned']);

        $parentData = $data['versionData'][$parent->id];
        $this->assertSame($parent->title, $parentData['content']['title']);
        $this->assertSame('H5P.Foobar 1.2.3', $parentData['content']['library']);

        $childData = $data['versionData'][$child->id];
        $this->assertSame($child->title, $childData['content']['title']);
        $this->assertSame($library->getLibraryString(true), $childData['content']['library']);
    }

    public function testIndex_unversionedContent(): void
    {
        $user = new GenericUser([
            'roles' => ['superadmin'],
            'name' => 'Just Testing',
        ]);
        $content = QuestionSet::factory()->create();

        $result = $this->withSession(['user' => $user])
            ->get(route('admin.support.versioning', ['contentId' => $content->id]))
            ->assertOk()
            ->original;

        $this->assertInstanceOf(View::class, $result);

        $data = $result->getData();
        $this->assertEquals($data['contentId'], $content->id);
        $this->assertEmpty($data['versionData']);
        $this->assertFalse($data['isContentVersioned']);
    }
}
