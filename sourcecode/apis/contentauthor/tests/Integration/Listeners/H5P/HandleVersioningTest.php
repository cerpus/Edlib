<?php

declare(strict_types=1);

namespace Tests\Integration\Listeners\H5P;

use App\Content;
use App\ContentVersions;
use App\Events\H5PWasSaved;
use App\H5PContent;
use App\Listeners\H5P\HandleVersioning;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\Request;
use Tests\TestCase;

class HandleVersioningTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    public function testHandle(): void
    {
        $h5p = H5PContent::factory()->create();
        $event = new H5PWasSaved($h5p, new Request(), ContentVersions::PURPOSE_CREATE, null);
        (new HandleVersioning())->handle($event);

        $h5p->refresh();
        $this->assertNotNull($h5p->version_id);

        $this->assertDatabaseCount('content_versions', 1);
        $this->assertDatabaseHas('content_versions', [
            'id' => $h5p->version_id,
            'content_id' => $h5p->id,
            'content_type' => Content::TYPE_H5P,
            'parent_id' => null,
            'version_purpose' => ContentVersions::PURPOSE_CREATE,
        ]);
    }

    public function testHandle_newVersion(): void
    {
        $h5p = H5PContent::factory()->create([
            'version_id' => $this->faker->uuid,
        ]);
        $originalVersion = ContentVersions::factory()->create([
            'id' => $h5p->version_id,
            'content_id' => $h5p->id,
            'content_type' => Content::TYPE_H5P,
            'parent_id' => null,
            'version_purpose' => ContentVersions::PURPOSE_CREATE,
        ]);
        $event = new H5PWasSaved($h5p, new Request(), ContentVersions::PURPOSE_UPDATE, $h5p);
        (new HandleVersioning())->handle($event);

        $h5p->refresh();
        $this->assertNotNull($h5p->version_id);
        $this->assertNotSame($h5p->version_id, $originalVersion->id);

        $this->assertDatabaseCount('content_versions', 2);
        $this->assertDatabaseHas('content_versions', [
            'id' => $h5p->version_id,
            'content_id' => $h5p->id,
            'content_type' => Content::TYPE_H5P,
            'parent_id' => $originalVersion->id,
            'version_purpose' => ContentVersions::PURPOSE_UPDATE,
        ]);
    }

    public function testHandle_withParentWithoutVersion(): void
    {
        $parent = H5PContent::factory()->create();
        $h5p = H5PContent::factory()->create();
        $event = new H5PWasSaved($h5p, new Request(), ContentVersions::PURPOSE_UPDATE, $parent);
        (new HandleVersioning())->handle($event);

        $parent->refresh();
        $h5p->refresh();
        $this->assertNotNull($parent->version_id);
        $this->assertNotNull($h5p->version_id);

        $this->assertDatabaseCount('content_versions', 2);
        $this->assertDatabaseHas('content_versions', [
            'id' => $parent->version_id,
            'content_id' => $parent->id,
            'content_type' => Content::TYPE_H5P,
            'parent_id' => null,
            'version_purpose' => ContentVersions::PURPOSE_CREATE,
        ]);
        $this->assertDatabaseHas('content_versions', [
            'id' => $h5p->version_id,
            'content_id' => $h5p->id,
            'content_type' => Content::TYPE_H5P,
            'parent_id' => $parent->version_id,
            'version_purpose' => ContentVersions::PURPOSE_UPDATE,
        ]);
    }

    public function testHandle_withUnsavedParent(): void
    {
        $parent = H5PContent::factory()->make();
        $h5p = H5PContent::factory()->create();
        $event = new H5PWasSaved($h5p, new Request(), ContentVersions::PURPOSE_UPDATE, $parent);
        (new HandleVersioning())->handle($event);

        $h5p->refresh();
        $this->assertNotNull($h5p->version_id);

        $this->assertDatabaseCount('content_versions', 1);
        $this->assertDatabaseHas('content_versions', [
            'id' => $h5p->version_id,
            'content_id' => $h5p->id,
            'content_type' => Content::TYPE_H5P,
            'parent_id' => $parent->version_id,
            'version_purpose' => ContentVersions::PURPOSE_UPDATE,
        ]);
    }
}
