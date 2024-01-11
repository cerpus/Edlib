<?php

declare(strict_types=1);

namespace Tests\Integration\Listeners\H5P\Copy;

use App\Content;
use App\ContentVersion;
use App\Events\H5PWasCopied;
use App\H5PContent;
use App\Listeners\H5P\Copy\HandleVersioning;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class HandleVersioningTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    public function testHandle(): void
    {
        $originalH5p = H5PContent::factory()->create([
            'version_id' => $this->faker->uuid,
        ]);
        $originalVersion = ContentVersion::factory()->create([
            'id' => $originalH5p->version_id,
            'content_id' => $originalH5p->id,
            'content_type' => Content::TYPE_H5P,
            'parent_id' => null,
            'version_purpose' => ContentVersion::PURPOSE_CREATE,
        ]);
        $newH5p = H5PContent::factory()->create();
        $event = new H5PWasCopied($originalH5p, $newH5p, ContentVersion::PURPOSE_COPY);
        (new HandleVersioning())->handle($event);

        $newH5p->refresh();
        $this->assertNotNull($newH5p->version_id);

        $this->assertDatabaseCount('content_versions', 2);
        $this->assertDatabaseHas('content_versions', [
            'id' => $newH5p->version_id,
            'content_id' => $newH5p->id,
            'content_type' => Content::TYPE_H5P,
            'parent_id' => $originalVersion->id,
            'version_purpose' => ContentVersion::PURPOSE_COPY,
        ]);
    }
}
