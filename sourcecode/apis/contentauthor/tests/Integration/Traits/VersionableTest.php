<?php

declare(strict_types=1);

namespace Tests\Integration\Traits;

use App\ContentVersion;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\Integration\Traits\Stubs\VersionableStubClass;
use Tests\TestCase;

class VersionableTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    public function test_fetchVersionData_success(): void
    {
        $versionable = new VersionableStubClass();

        $versionData = ContentVersion::factory()->create([
            'id' => $versionable->version_id,
        ]);
        $result = $versionable->fetchVersionData();

        $this->assertSame($result->id, $versionData->id);
    }

    public function test_getParent(): void
    {
        $versionable = new VersionableStubClass();

        $parent = ContentVersion::factory()->create();
        ContentVersion::factory()->create([
            'id' => $versionable->version_id,
            'parent_id' => $parent->id,
        ]);
        $result = $versionable->getParent();

        $this->assertSame($result->id, $parent->id);
    }

    public function test_getParentId(): void
    {
        $versionable = new VersionableStubClass();

        $grandParent = ContentVersion::factory()->create();
        $parent = ContentVersion::factory()->create([
            'parent_id' => $grandParent->id,
        ]);
        ContentVersion::factory()->create([
            'id' => $versionable->version_id,
            'parent_id' => $parent->id,
        ]);

        $result = $versionable->getParentIds();

        $this->assertCount(2, $result);
        $this->assertContains((string) $parent->content_id, $result);
        $this->assertContains((string) $grandParent->content_id, $result);
    }

    public function test_getChildren(): void
    {
        $versionable = new VersionableStubClass();

        $version = ContentVersion::factory()->create([
            'id' => $versionable->version_id,
        ]);
        $child1 = ContentVersion::factory()->create([
            'parent_id' => $version->id,
        ]);
        $child2 = ContentVersion::factory()->create([
            'parent_id' => $version->id,
        ]);

        $result = $versionable->getChildren();

        $this->assertCount(2, $result);
        $this->assertSame($child1->id, $result[0]['id']);
        $this->assertSame($child2->id, $result[1]['id']);
    }

    public function test_getVersion_success(): void
    {
        $versionable = new VersionableStubClass();
        $version = ContentVersion::factory()->create([
            'id' => $versionable->version_id,
        ]);

        $this->assertSame($version->id, $versionable->getVersion()->id);
    }

    public function test_getVersion_missingVersion(): void
    {
        $versionable = new VersionableStubClass();

        $this->assertNull($versionable->getVersion());
    }

    public function test_getVersion_notVersionable(): void
    {
        $versionable = new VersionableStubClass();
        $versionable->version_id = '';

        $this->assertNull($versionable->getVersion());
    }
}
