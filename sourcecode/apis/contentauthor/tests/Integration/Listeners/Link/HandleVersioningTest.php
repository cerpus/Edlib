<?php

declare(strict_types=1);

namespace Tests\Integration\Listeners\Link;

use App\Content;
use App\ContentVersions;
use App\Events\LinkWasSaved;
use App\Link;
use App\Listeners\Link\HandleVersioning;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class HandleVersioningTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    public function testHandle(): void
    {
        $link = Link::factory()->create();
        $event = new LinkWasSaved($link, ContentVersions::PURPOSE_CREATE);
        (new HandleVersioning())->handle($event);

        $link->refresh();
        $this->assertNotNull($link->version_id);

        $this->assertDatabaseCount('content_versions', 1);
        $this->assertDatabaseHas('content_versions', [
            'id' => $link->version_id,
            'content_id' => $link->id,
            'content_type' => Content::TYPE_LINK,
            'parent_id' => null,
            'version_purpose' => ContentVersions::PURPOSE_CREATE,
        ]);
    }

    public function testHandle_newVersion(): void
    {
        $link = Link::factory()->create([
            'version_id' => $this->faker->uuid,
        ]);
        $parentVersion = ContentVersions::factory()->create([
            'id' => $link->version_id,
            'content_id' => $link->id,
            'content_type' => Content::TYPE_LINK,
            'version_purpose' => ContentVersions::PURPOSE_CREATE,
            'parent_id' => null,
        ]);
        $event = new LinkWasSaved($link, ContentVersions::PURPOSE_UPDATE);
        (new HandleVersioning())->handle($event);

        $link->refresh();
        $this->assertNotNull($link->version_id);
        $this->assertNotSame($link->version_id, $parentVersion->id);

        $this->assertDatabaseCount('content_versions', 2);
        $this->assertDatabaseHas('content_versions', [
            'id' => $link->version_id,
            'content_id' => $link->id,
            'content_type' => Content::TYPE_LINK,
            'parent_id' => $parentVersion->id,
            'version_purpose' => ContentVersions::PURPOSE_UPDATE,
        ]);
    }
}
