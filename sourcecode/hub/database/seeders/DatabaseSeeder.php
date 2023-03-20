<?php

namespace Database\Seeders;

use App\Models\Content;
use App\Models\ContentVersion;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        Content::factory()
            ->has(ContentVersion::factory()->count(5), 'versions')
            ->count(40)
            ->create();
    }
}
