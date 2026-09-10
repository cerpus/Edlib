<?php

declare(strict_types=1);

namespace Tests\Integration\Http\Controllers\Admin;

use App\Console\Commands\H5PClearCachedAssets;
use App\H5PLibrariesCachedAssets;
use App\Libraries\DataObjects\ContentStorageSettings;
use Illuminate\Auth\GenericUser;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Tests\TestCase;

class CachedFilesControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_index(): void
    {
        Storage::fake();
        $user = new GenericUser([
            'name' => 'Admin',
            'roles' => ['superadmin'],
        ]);

        $result = $this->withSession(['user' => $user])
            ->get(route('admin.cached-files.index'))
            ->assertOk()
            ->original;

        $this->assertInstanceOf(View::class, $result);
        $data = $result->getData();
        $this->assertArrayHasKey('cachedFilesCount', $data);
        $this->assertArrayHasKey('cachedAssetRowsCount', $data);
        $this->assertArrayHasKey('isJobRunning', $data);
        $this->assertFalse($data['isJobRunning']);

        Cache::put(H5PClearCachedAssets::CACHE_KEY_RUNNING, true, 300);

        $resultRunning = $this->withSession(['user' => $user])
            ->get(route('admin.cached-files.index'))
            ->assertOk()
            ->original;

        $dataRunning = $resultRunning->getData();
        $this->assertTrue($dataRunning['isJobRunning']);

        Cache::forget(H5PClearCachedAssets::CACHE_KEY_RUNNING);
    }

    public function test_status(): void
    {
        $user = new GenericUser([
            'name' => 'Admin',
            'roles' => ['superadmin'],
        ]);

        $this->withSession(['user' => $user])
            ->get(route('admin.cached-files.status'))
            ->assertOk()
            ->assertExactJson(['isRunning' => false]);

        Cache::put(H5PClearCachedAssets::CACHE_KEY_RUNNING, true, 300);

        $this->withSession(['user' => $user])
            ->get(route('admin.cached-files.status'))
            ->assertOk()
            ->assertExactJson(['isRunning' => true]);

        Cache::forget(H5PClearCachedAssets::CACHE_KEY_RUNNING);
    }

    public function test_delete(): void
    {
        Storage::fake();
        $disk = Storage::disk();
        $disk->put(ContentStorageSettings::CACHEDASSETS_DIR . '/test.js', 'console.log("test");');
        H5PLibrariesCachedAssets::create([
            'hash' => 'testhash',
            'library_id' => 1,
        ]);

        $user = new GenericUser([
            'name' => 'Admin',
            'roles' => ['superadmin'],
        ]);

        $this->withSession(['user' => $user])
            ->post(route('admin.cached-files.delete'))
            ->assertRedirect(route('admin.cached-files.index'))
            ->assertSessionHas('message', 'Cached files deletion has been queued.');

        $this->assertEquals(0, H5PLibrariesCachedAssets::count());
        $this->assertFalse($disk->exists(ContentStorageSettings::CACHEDASSETS_DIR . '/test.js'));
        $this->assertFalse(Cache::has(H5PClearCachedAssets::CACHE_KEY_RUNNING));
    }

    public function test_command_clears_cache_key(): void
    {
        Storage::fake();
        Artisan::call('h5p:clear-cached-assets');
        $this->assertFalse(Cache::has(H5PClearCachedAssets::CACHE_KEY_RUNNING));
    }

    public function test_unlock(): void
    {
        $user = new GenericUser([
            'name' => 'Admin',
            'roles' => ['superadmin'],
        ]);

        Cache::put(H5PClearCachedAssets::CACHE_KEY_RUNNING, true, 300);
        $this->assertTrue(Cache::has(H5PClearCachedAssets::CACHE_KEY_RUNNING));

        $this->withSession(['user' => $user])
            ->post(route('admin.cached-files.unlock'))
            ->assertRedirect(route('admin.cached-files.index'))
            ->assertSessionHas('message', 'The deletion lock has been removed.');

        $this->assertFalse(Cache::has(H5PClearCachedAssets::CACHE_KEY_RUNNING));
    }

    public function test_clear_lock(): void
    {
        Cache::put(H5PClearCachedAssets::CACHE_KEY_RUNNING, true, 300);
        $this->assertTrue(Cache::has(H5PClearCachedAssets::CACHE_KEY_RUNNING));

        H5PClearCachedAssets::clearLock();
        $this->assertFalse(Cache::has(H5PClearCachedAssets::CACHE_KEY_RUNNING));
    }

    public function test_command_unlock_option(): void
    {
        Cache::put(H5PClearCachedAssets::CACHE_KEY_RUNNING, true, 300);
        $this->assertTrue(Cache::has(H5PClearCachedAssets::CACHE_KEY_RUNNING));

        Artisan::call('h5p:clear-cached-assets', ['--unlock' => true]);
        $this->assertFalse(Cache::has(H5PClearCachedAssets::CACHE_KEY_RUNNING));
    }
}
