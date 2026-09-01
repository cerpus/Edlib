<?php

declare(strict_types=1);

namespace Tests\Integration\Http\Controllers\Admin;

use App\H5PLibrariesCachedAssets;
use App\Libraries\DataObjects\ContentStorageSettings;
use Illuminate\Auth\GenericUser;
use Illuminate\Foundation\Testing\RefreshDatabase;
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
            ->assertRedirect(route('admin.cached-files.index'));

        $this->assertEquals(0, H5PLibrariesCachedAssets::count());
        $this->assertFalse($disk->exists(ContentStorageSettings::CACHEDASSETS_DIR . '/test.js'));
    }
}
