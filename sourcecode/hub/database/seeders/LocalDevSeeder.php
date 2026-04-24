<?php

namespace Database\Seeders;

use App\Enums\LtiToolEditMode;
use App\Models\LtiTool;
use App\Models\LtiToolExtra;
use App\Models\User;
use Cerpus\EdlibResourceKit\Oauth1\Credentials;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class LocalDevSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (app()->environment('local')) {
            User::factory()
                ->admin()
                ->name('Admin')
                ->withEmail('admin@edlib.test')
                ->create(['password' => Hash::make('secret')]);

            $ltiTool = LtiTool::factory()
                ->withName('Content Author')
                ->launchUrl('https://ca.edlib.test/lti-content/create')
                ->withCredentials(new Credentials('h5p', 'secret2'))
                ->defaultPublished()
                ->defaultShared()
                ->editMode(LtiToolEditMode::DeepLinkingRequestToContentUrl)
                ->sendName()
                ->sendEmail()
                ->create();

            LtiToolExtra::factory()
                ->admin()
                ->name('CA Admin')
                ->slug('ca-admin')
                ->ltiLaunchUrl('https://ca.edlib.test/lti/admin')
                ->ltiTool($ltiTool)
                ->create();
        }
    }
}
