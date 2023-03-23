<?php

namespace Database\Seeders;

use App\Models\Content;
use App\Models\ContentVersion;
use App\Models\LtiResource;
use App\Models\LtiTool;
use App\Models\LtiVersion;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $ltiTool = LtiTool::factory()
            ->state([
                'name' => 'Content Author',
                'lti_version' => LtiVersion::Lti1_1,
                'creator_launch_url' => 'https://ca.edlib.local/lti-content/create',
            ])
            ->create();

        $ltiResourceFactory = LtiResource::factory()
            ->state([
                'lti_tool_id' => $ltiTool->id,
                'view_launch_url' => 'https://ca.edlib.local/lti-content/1',
                'edit_launch_url' => "https://ca.edlib.local/lti-content/1/edit",
            ]);

        $contentVersionFactory = ContentVersion::factory()
            ->state(['lti_resource_id' => $ltiResourceFactory])
            ->count(5);

        Content::factory()
            ->has($contentVersionFactory, 'versions')
            ->count(40)
            ->create();
    }
}
