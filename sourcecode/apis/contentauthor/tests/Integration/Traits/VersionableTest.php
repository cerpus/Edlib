<?php

declare(strict_types=1);

namespace Tests\Integration\Traits;

use App\ContentVersions;
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

        $versionData = ContentVersions::factory()->create([
            'id' => $versionable->version_id,
        ]);
        $result = $versionable->fetchVersionData();

        $this->assertSame($result->id, $versionData->id);
    }

    public function test_getParent(): void
    {
        $versionable = new VersionableStubClass();

        $parent = ContentVersions::factory()->create();
        ContentVersions::factory()->create([
            'id' => $versionable->version_id,
            'parent_id' => $parent->id,
        ]);
        $result = $versionable->getParent();

        $this->assertSame($result->id, $parent->id);
    }

    public function test_getParentId(): void
    {
        $versionable = new VersionableStubClass();

        $grandParent = ContentVersions::factory()->create();
        $parent = ContentVersions::factory()->create([
            'parent_id' => $grandParent->id,
        ]);
        ContentVersions::factory()->create([
            'id' => $versionable->version_id,
            'parent_id' => $parent->id,
        ]);

        $result = $versionable->getParentIds();

        $this->assertCount(2, $result);
        $this->assertContains((string)$parent->content_id, $result);
        $this->assertContains((string)$grandParent->content_id, $result);
    }

    public function test_getChildren(): void
    {
        $versionable = new VersionableStubClass();

        $version = ContentVersions::factory()->create([
            'id' => $versionable->version_id,
        ]);
        $child1 = ContentVersions::factory()->create([
            'parent_id' => $version->id,
        ]);
        $child2 = ContentVersions::factory()->create([
            'parent_id' => $version->id,
        ]);

        $result = $versionable->getChildren();

        $this->assertCount(2, $result);
        $this->assertSame($child1->id, $result[0]['id']);
        $this->assertSame($child2->id, $result[1]['id']);
    }
}
