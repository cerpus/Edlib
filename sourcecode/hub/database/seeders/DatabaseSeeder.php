<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\LtiVersion;
use App\Models\Content;
use App\Models\ContentVersion;
use App\Models\LtiTool;
use App\Models\User;
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
                'name' => 'Edlib 3',
                'lti_version' => LtiVersion::Lti1_1,
                'creator_launch_url' => 'https://hub-test.edlib.test/lti/samples/deep-link',
            ])
            ->create();

        $contentVersionFactory = ContentVersion::factory()
            ->state([
                'lti_tool_id' => $ltiTool->id,
                'lti_launch_url' => 'https://hub-test.edlib.test/lti/samples/presentation',
            ])
            ->count(5);

        Content::factory()
            ->has($contentVersionFactory, 'versions')
            ->hasAttached(User::factory(), [
                'role' => 'owner',
            ], 'users')
            ->count(40)
            ->create();
    }
}
