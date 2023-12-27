<?php

declare(strict_types=1);

namespace Tests\Integration\Listeners\Game;

use App\Content;
use App\ContentVersions;
use App\Events\GameWasSaved;
use App\Game;
use App\Libraries\DataObjects\ResourceMetadataDataObject;
use App\Listeners\Game\HandleVersioning;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class HandleVersioningTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    public function testHandle(): void
    {
        $game = Game::factory()->create();
        $metadata = new ResourceMetadataDataObject('BY', null, ContentVersions::PURPOSE_CREATE);
        $event = new GameWasSaved($game, $metadata);
        (new HandleVersioning())->handle($event);

        $game->refresh();
        $this->assertNotNull($game->version_id);

        $this->assertDatabaseCount('content_versions', 1);
        $this->assertDatabaseHas('content_versions', [
            'id' => $game->version_id,
            'content_id' => $game->id,
            'content_type' => Content::TYPE_GAME,
            'parent_id' => null,
            'version_purpose' => ContentVersions::PURPOSE_CREATE,
        ]);
    }

    public function testHandle_newVersion(): void
    {
        $game = Game::factory()->create([
            'version_id' => $this->faker->uuid,
        ]);
        $originalVersion = ContentVersions::factory()->create([
            'id' => $game->version_id,
            'content_id' => $game->id,
            'content_type' => Content::TYPE_GAME,
            'parent_id' => null,
            'version_purpose' => ContentVersions::PURPOSE_CREATE,
        ]);
        $metadata = new ResourceMetadataDataObject('BY', null, ContentVersions::PURPOSE_UPDATE);
        $event = new GameWasSaved($game, $metadata);
        (new HandleVersioning())->handle($event);

        $game->refresh();
        $this->assertNotNull($game->version_id);
        $this->assertNotSame($game->version_id, $originalVersion->id);

        $this->assertDatabaseCount('content_versions', 2);
        $this->assertDatabaseHas('content_versions', [
            'id' => $game->version_id,
            'content_id' => $game->id,
            'content_type' => Content::TYPE_GAME,
            'parent_id' => $originalVersion->id,
            'version_purpose' => ContentVersions::PURPOSE_UPDATE,
        ]);
    }
}
