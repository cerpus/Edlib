<?php

namespace Tests\Feature;

use App\Models\Content;
use App\Models\ContentVersion;
use App\Models\LtiResource;
use App\Models\LtiTool;
use App\Models\LtiVersion;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class ContentTest extends TestCase
{
    use RefreshDatabase;

    public function testExploresContent(): void
    {
        $content = Content::factory()
            ->has(ContentVersion::factory(), 'versions')
            ->create();

        $this->assertIsString($content->latestVersion?->resource?->title);

        $this->get('/content')
            ->assertOk()
            ->assertSee($content->latestVersion->resource->title);
    }

    public function testPreviewsLti11Content(): void
    {
        $tool = LtiTool::factory()
            ->state(['lti_version' => LtiVersion::Lti1_1])
            ->create();

        $resource = LtiResource::factory()
            ->state(['lti_tool_id' => $tool->id])
            ->create();

        $content = Content::factory()
            ->has(ContentVersion::factory()->state([
                'lti_resource_id' => $resource->id,
            ]), 'versions')
            ->create();

        // TODO: assert that the frame and form exist and stuff
        $this->get("/content/{$content->id}")
            ->assertOk();
    }
}
