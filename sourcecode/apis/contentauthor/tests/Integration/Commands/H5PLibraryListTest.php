<?php

declare(strict_types=1);

namespace Tests\Integration\Commands;

use App\H5PLibrariesHubCache;
use App\H5PLibrary;
use App\H5POption;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class H5PLibraryListTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    private Carbon $cacheUpdated;

    protected function setUp(): void
    {
        parent::setUp();

        $this->createTestData();
    }

    public function testCanListInstalled(): void
    {
        $this->artisan('h5p:library-list:installed')
            ->expectsOutput('Libraries found: 6')
            ->expectsTable(
                ['Name', 'Version', 'Content type'],
                [
                    ['test.Audio', '1.2.3', 'Yes'],
                    ['test.Audio', '1.10.4', 'Yes'],
                    ['test.Core', '1.8.24', 'Yes'],
                    ['test.Table', '1.0.4', 'No'],
                    ['test.Text', '2.19.42', 'Yes'],
                    ['test.Video', '2.1.8', 'Yes'],
                ],
            );
    }

    public function testCanListInstalledOnlyRunnable(): void
    {
        $this->artisan('h5p:library-list:installed -r')
            ->expectsOutput('Libraries found: 5')
            ->expectsTable(
                ['Name', 'Version', 'Content type'],
                [
                    ['test.Audio', '1.2.3', 'Yes'],
                    ['test.Audio', '1.10.4', 'Yes'],
                    ['test.Core', '1.8.24', 'Yes'],
                    ['test.Text', '2.19.42', 'Yes'],
                    ['test.Video', '2.1.8', 'Yes'],
                ],
            );
    }

    public function testCanListInstalledSpecificLibrary(): void
    {
        $this->artisan('h5p:library-list:installed -l test.video')
            ->expectsOutput('Libraries found: 1')
            ->expectsTable(
                ['Name', 'Version', 'Content type'],
                [
                    ['test.Video', '2.1.8', 'Yes'],
                ],
            );
    }

    public function testCanListInstalledSpecificLibraryOnlyRunnable(): void
    {
        $this->artisan('h5p:library-list:installed -r -l test.text')
            ->expectsOutput('Libraries found: 1')
            ->expectsTable(
                ['Name', 'Version', 'Content type'],
                [
                    ['test.Text', '2.19.42', 'Yes'],
                ],
            );
    }

    public function testCanListAvailable(): void
    {
        $this->artisan('h5p:library-list:available')
            ->expectsOutput('Library cache updated: ' . $this->cacheUpdated->format('Y-m-d H:i:s e'))
            ->expectsOutput('Libraries found: 1')
            ->expectsTable(
                ['Name', 'Version', 'H5P Core version', 'Creator'],
                [
                    ['test.Image', '1.4.6', '1.23', 'Boaty McBoatface'],
                ],
            );
    }

    public function testCanListAllAvailable(): void
    {
        $this->artisan('h5p:library-list:available -a')
            ->expectsOutput('Library cache updated: ' . $this->cacheUpdated->format('Y-m-d H:i:s e'))
            ->expectsOutput('Libraries found: 3')
            ->expectsTable(
                ['Name', 'Version', 'H5P Core version', 'Creator'],
                [
                    ['test.Audio', '1.2.3', '1.24', 'Testy Tester'],
                    ['test.Image', '1.4.6', '1.23', 'Boaty McBoatface'],
                    ['test.Video', '2.1.10', '1.22', 'Boaty McBoatface'],
                ],
            );
    }

    public function testCanListAvailableIgnoringCoreVersion(): void
    {
        $this->artisan('h5p:library-list:available --ignore-core-version')
            ->expectsOutput('Library cache updated: ' . $this->cacheUpdated->format('Y-m-d H:i:s e'))
            ->expectsOutput('Libraries found: 2')
            ->expectsTable(
                ['Name', 'Version', 'H5P Core version', 'Creator'],
                [
                    ['test.Image', '1.4.6', '1.23', 'Boaty McBoatface'],
                    ['test.Lab', '2.2.42', '1.87', 'Boaty McBoatface'],
                ],
            );
    }

    public function testCanListAllAvailableIgnoringCoreVersion(): void
    {
        $this->artisan('h5p:library-list:available -a --ignore-core-version')
            ->expectsOutput('Library cache updated: ' . $this->cacheUpdated->format('Y-m-d H:i:s e'))
            ->expectsOutput('Libraries found: 5')
            ->expectsTable(
                ['Name', 'Version', 'H5P Core version', 'Creator'],
                [
                    ['test.Audio', '1.2.3', '1.24', 'Testy Tester'],
                    ['test.Core', '1.8.42', '1.73', 'Ducky McDuckface'],
                    ['test.Image', '1.4.6', '1.23', 'Boaty McBoatface'],
                    ['test.Lab', '2.2.42', '1.87', 'Boaty McBoatface'],
                    ['test.Video', '2.1.10', '1.22', 'Boaty McBoatface'],
                ],
            );
    }

    public function testCanListAvailableSpecificLibrary(): void
    {
        $this->artisan('h5p:library-list:available -l test.image')
            ->expectsOutput('Library cache updated: ' . $this->cacheUpdated->format('Y-m-d H:i:s e'))
            ->expectsOutput('Libraries found: 1')
            ->expectsTable(
                ['Name', 'Version', 'H5P Core version', 'Creator'],
                [
                    ['test.Image', '1.4.6', '1.23', 'Boaty McBoatface'],
                ],
            );
    }

    public function testCanListAllAvailableLibrary(): void
    {
        $this->artisan('h5p:library-list:available -a -l test.audio')
            ->expectsOutput('Library cache updated: ' . $this->cacheUpdated->format('Y-m-d H:i:s e'))
            ->expectsOutput('Installed H5P Core version: 1.27')
            ->expectsOutput('Libraries found: 1')
            ->expectsTable(
                ['Name', 'Version', 'H5P Core version', 'Creator'],
                [
                    ['test.Audio', '1.2.3', '1.24', 'Testy Tester'],
                ],
            );
    }

    public function testCanListOutdated(): void
    {
        $this->artisan('h5p:library-list:outdated')
            ->expectsOutput('Library cache updated: ' . $this->cacheUpdated->format('Y-m-d H:i:s e'))
            ->expectsOutput('Libraries found: 1')
            ->expectsTable(
                ['Name', 'Installed', 'On h5p.org Hub', 'H5P Core version required'],
                [
                    ['test.Video', '2.1.8', '2.1.10', '1.22'],
                ],
            );
    }

    public function testCanListOutdatedAll(): void
    {
        $this->artisan('h5p:library-list:outdated -a')
            ->expectsOutput('Library cache updated: ' . $this->cacheUpdated->format('Y-m-d H:i:s e'))
            ->expectsOutput('Libraries found: 2')
            ->expectsTable(
                ['Name', 'Installed', 'On h5p.org Hub', 'H5P Core version required'],
                [
                    ['test.Audio', '1.10.4', '1.2.3', '1.24'],
                    ['test.Video', '2.1.8', '2.1.10', '1.22'],
                ],
            );
    }

    public function testCanListOutdatedIgnoringCoreVersion(): void
    {
        $this->artisan('h5p:library-list:outdated --ignore-core-version')
            ->expectsOutput('Installed H5P Core version: 1.27')
            ->expectsOutput('Library cache updated: ' . $this->cacheUpdated->format('Y-m-d H:i:s e'))
            ->expectsOutput('Libraries found: 2')
            ->expectsTable(
                ['Name', 'Installed', 'On h5p.org Hub', 'H5P Core version required'],
                [
                    ['test.Core', '1.8.24', '1.8.42', '1.73'],
                    ['test.Video', '2.1.8', '2.1.10', '1.22'],
                ],
            );
    }

    public function testCanListOutdatedSpecificLibrary(): void
    {
        $this->artisan('h5p:library-list:outdated -l test.video')
            ->expectsOutput('Library cache updated: ' . $this->cacheUpdated->format('Y-m-d H:i:s e'))
            ->expectsOutput('Libraries found: 1')
            ->expectsTable(
                ['Name', 'Installed', 'On h5p.org Hub', 'H5P Core version required'],
                [
                    ['test.Video', '2.1.8', '2.1.10', '1.22'],
                ],
            );
    }

    /**
     * test.Audio       Two versions installed, hub version and one newer
     * test.Video       Installed, hub has newer version
     * test.Image       Not installed
     * test.Text        Installed, not on hub
     * test.Core        Installed, update that requires newer H5P Core version exist
     * test.Lab         Not installed, requires newer H5P Core version than installed
     * test.Table       Not runnable library, i.e. not a content type
     */
    private function createTestData(): void
    {
        $this->cacheUpdated = Carbon::now();

        H5PLibrary::factory()->create([
            'name' => 'test.Audio',
            'major_version' => '1',
            'minor_version' => '2',
            'patch_version' => '3',
        ]);
        H5PLibrary::factory()->create([
            'name' => 'test.Video',
            'major_version' => '2',
            'minor_version' => '1',
            'patch_version' => '8',
        ]);
        H5PLibrary::factory()->create([
            'name' => 'test.Audio',
            'major_version' => '1',
            'minor_version' => '10',
            'patch_version' => '4',
        ]);
        H5PLibrary::factory()->create([
            'name' => 'test.Text',
            'major_version' => '2',
            'minor_version' => '19',
            'patch_version' => '42',
        ]);
        H5PLibrary::factory()->create([
            'name' => 'test.Core',
            'major_version' => 1,
            'minor_version' => 8,
            'patch_version' => 24,
        ]);
        H5PLibrary::factory()->create([
            'name' => 'test.Table',
            'major_version' => '1',
            'minor_version' => '0',
            'patch_version' => '4',
            'runnable' => false,
        ]);

        H5POption::create([
            'option_name' => 'content_type_cache_updated_at',
            'option_value' => $this->cacheUpdated->timestamp,
            'autoload' => '',
        ]);
        H5PLibrariesHubCache::factory()->create([
            'name' => 'test.Audio',
            'major_version' => 1,
            'minor_version' => 2,
            'patch_version' => 3,
            'owner' => 'Testy Tester',
            'h5p_major_version' => 1,
            'h5p_minor_version' => 24,
        ]);
        H5PLibrariesHubCache::factory()->create([
            'name' => 'test.Image',
            'major_version' => 1,
            'minor_version' => 4,
            'patch_version' => 6,
            'owner' => 'Boaty McBoatface',
            'h5p_major_version' => 1,
            'h5p_minor_version' => 23,
        ]);
        H5PLibrariesHubCache::factory()->create([
            'name' => 'test.Video',
            'major_version' => 2,
            'minor_version' => 1,
            'patch_version' => 10,
            'owner' => 'Boaty McBoatface',
            'h5p_major_version' => 1,
            'h5p_minor_version' => 22,
        ]);
        H5PLibrariesHubCache::factory()->create([
            'name' => 'test.Core',
            'major_version' => 1,
            'minor_version' => 8,
            'patch_version' => 42,
            'owner' => 'Ducky McDuckface',
            'h5p_major_version' => 1,
            'h5p_minor_version' => 73,
        ]);
        H5PLibrariesHubCache::factory()->create([
            'name' => 'test.Lab',
            'major_version' => 2,
            'minor_version' => 2,
            'patch_version' => 42,
            'owner' => 'Boaty McBoatface',
            'h5p_major_version' => 1,
            'h5p_minor_version' => 87,
        ]);
    }
}
