<?php

namespace Tests\Integration\Http\Controllers\API;

use App\Content;
use App\ContentVersion;
use App\H5PContent;
use App\Http\Libraries\License;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Config;
use Tests\TestCase;

class ContentInfoControllerTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    public function testList(): void
    {
        $resource1 = H5PContent::factory()->create([
            'id' => 1,
            'created_at' => Carbon::now()->subDays(7),
            'updated_at' => Carbon::now(),
            'license' => License::LICENSE_BY,
        ]);
        $resource2 = H5PContent::factory()->create([
            'id' => 2,
            'created_at' => Carbon::now()->subDays(5),
            'updated_at' => Carbon::now(),
            'license' => License::LICENSE_CC,
        ]);
        $resource3 = H5PContent::factory()->create([
            'id' => 3,
            'created_at' => Carbon::now()->subDay(),
            'updated_at' => Carbon::now(),
            'license' => License::LICENSE_EDLIB,
        ]);

        $this->get('v1/content')
            ->assertOk()
            ->assertJson([
                'pagination' => [
                    'totalCount' => 3,
                    'offset' => 0,
                    'limit' => 50,
                ],
                'resources' => [
                    [
                        'externalSystemId' => strval($resource1->id),
                        'title' => $resource1->title,
                        'license' => License::LICENSE_BY,
                    ],
                    [
                        'externalSystemId' => strval($resource2->id),
                        'title' => $resource2->title,
                        'license' => License::LICENSE_CC,
                    ],
                    [
                        'externalSystemId' => strval($resource3->id),
                        'title' => $resource3->title,
                        'license' => License::LICENSE_EDLIB,
                    ],
                ],
            ]);
    }

    public function test_getVersion_success(): void
    {
        $apiKey = $this->faker->uuid;
        Config::set('internal.key', $apiKey);

        $content = H5PContent::factory()->create([
            'version_id' => $this->faker->uuid,
        ]);

        $version = ContentVersion::factory()->create([
            'id' => $content->version_id,
            'content_id' => $content->id,
            'content_type' => Content::TYPE_H5P,
            'version_purpose' => ContentVersion::PURPOSE_CREATE,
        ]);

        $this->get('/internal/v1/content-version/' . $content->id, [
            'x-api-key' => $apiKey,
        ])
            ->assertOk()
            ->assertJson([
                'id' => $version->id,
                'versionPurpose' => ContentVersion::PURPOSE_CREATE,
                'externalSystemId' => $version->content_id,
                'externalSystemName' => 'contentauthor',
            ])
        ;
    }

    public function test_getVersion_fail(): void
    {
        $apiKey = $this->faker->uuid;
        Config::set('internal.key', $apiKey);

        $this->get('/internal/v1/content-version/' . $this->faker->numberBetween(), [
            'x-api-key' => $apiKey,
        ])
            ->assertNotFound();
    }

    public function test_getPreviousVersions(): void
    {
        $apiKey = $this->faker->uuid;
        Config::set('internal.key', $apiKey);

        $firstVersion = ContentVersion::factory()->create([
            'id' => $this->faker->uuid,
            'content_id' => $this->faker->numberBetween(),
            'content_type' => Content::TYPE_H5P,
            'version_purpose' => ContentVersion::PURPOSE_CREATE,
        ]);

        $secondVersion = ContentVersion::factory()->create([
            'id' => $this->faker->uuid,
            'content_id' => $this->faker->numberBetween(),
            'parent_id' => $firstVersion->id,
            'content_type' => Content::TYPE_H5P,
            'version_purpose' => ContentVersion::PURPOSE_UPDATE,
        ]);

        $thirdVersion = ContentVersion::factory()->create([
            'id' => $this->faker->uuid,
            'content_id' => $this->faker->numberBetween(),
            'parent_id' => $secondVersion->id,
            'content_type' => Content::TYPE_H5P,
            'version_purpose' => ContentVersion::PURPOSE_UPDATE,
        ]);

        $fourthVersion = ContentVersion::factory()->create([
            'id' => $this->faker->uuid,
            'content_id' => $this->faker->numberBetween(),
            'parent_id' => $thirdVersion->id,
            'content_type' => Content::TYPE_H5P,
            'version_purpose' => ContentVersion::PURPOSE_UPDATE,
        ]);

        $this->assertDatabaseCount('content_versions', 4);

        $this->get('/internal/v1/content-version/' . $fourthVersion->id . '/history', [
            'x-api-key' => $apiKey,
        ])
            ->assertOk()
            ->assertJsonCount(3)
            ->assertJson([
                [
                    'externalSystem' => 'contentauthor',
                    'externalReference' => $thirdVersion->content_id,
                ],
                [
                    'externalSystem' => 'contentauthor',
                    'externalReference' => $secondVersion->content_id,
                ],
                [
                    'externalSystem' => 'contentauthor',
                    'externalReference' => $firstVersion->content_id,
                ],
            ]);
    }

    public function test_getPreviousVersions_none(): void
    {
        $apiKey = $this->faker->uuid;
        Config::set('internal.key', $apiKey);

        $firstVersion = ContentVersion::factory()->create([
            'id' => $this->faker->uuid,
            'content_id' => $this->faker->numberBetween(),
            'content_type' => Content::TYPE_H5P,
            'version_purpose' => ContentVersion::PURPOSE_CREATE,
        ]);

        $this->assertDatabaseCount('content_versions', 1);

        $this->get('/internal/v1/content-version/' . $firstVersion->id . '/history', [
            'x-api-key' => $apiKey,
        ])
            ->assertOk()
            ->assertJsonCount(0);
    }

    public function test_getPreviousVersions_updateWithoutPrevious(): void
    {
        $apiKey = $this->faker->uuid;
        Config::set('internal.key', $apiKey);

        $firstVersion = ContentVersion::factory()->create([
            'id' => $this->faker->uuid,
            'content_id' => $this->faker->numberBetween(),
            'content_type' => Content::TYPE_H5P,
            'version_purpose' => ContentVersion::PURPOSE_UPDATE,
        ]);

        $secondVersion = ContentVersion::factory()->create([
            'id' => $this->faker->uuid,
            'content_id' => $this->faker->numberBetween(),
            'parent_id' => $firstVersion->id,
            'content_type' => Content::TYPE_H5P,
            'version_purpose' => ContentVersion::PURPOSE_UPDATE,
        ]);

        $this->assertDatabaseCount('content_versions', 2);

        $this->get('/internal/v1/content-version/' . $secondVersion->id . '/history', [
            'x-api-key' => $apiKey,
        ])
            ->assertOk()
            ->assertJsonCount(1)
            ->assertJson([
                [
                    'externalSystem' => 'contentauthor',
                    'externalReference' => $firstVersion->content_id,
                ],
            ]);
    }
}
