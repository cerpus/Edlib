<?php

namespace Tests\Integration\Libraries\H5P;

use App\H5PLibrary;
use App\Libraries\H5P\AjaxRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AjaxRequestTest extends TestCase
{
    use RefreshDatabase;

    public function test_libraryRebuild(): void
    {
        /** @var H5PLibrary $library */
        $library = H5PLibrary::factory()->create();
        /** @var H5PLibrary $preLib */
        $preLib = H5PLibrary::factory()->create(['name' => 'player', 'major_version' => 3, 'minor_version' => 14]);
        /** @var H5PLibrary $dynLib */
        $dynLib = H5PLibrary::factory()->create(['name' => 'H5P.Dynamic', 'major_version' => 2, 'minor_version' => 42, 'patch_version' => 3, 'patch_version_in_folder_name' => true]);
        /** @var H5PLibrary $edLib */
        $edLib = H5PLibrary::factory()->create(['name' => 'FontOk', 'major_version' => 1, 'minor_version' => 3]);

        $this->assertDatabaseEmpty('h5p_libraries_libraries');

        $validator = $this->createMock(\H5PValidator::class);
        $this->instance(\H5PValidator::class, $validator);
        $validator
            ->expects($this->exactly(4))
            ->method('getLibraryData')
            ->withConsecutive(['H5P.Foobar-1.2'], ['player-3.14'], ['H5P.Dynamic-2.42.3'], ['FontOk-1.3'])
            ->willReturnOnConsecutiveCalls([
                'preloadedDependencies' => [$preLib->getLibraryH5PFriendly()],
                'dynamicDependencies' => [$dynLib->getLibraryH5PFriendly()],
                'editorDependencies' => [$edLib->getLibraryH5PFriendly()],
            ], [], [], []);

        $this
            ->withSession(['isAdmin' => true])
            ->post('/ajax', ['action' => AjaxRequest::LIBRARY_REBUILD, 'libraryId' => $library->id])
            ->assertOk()
            ->assertJson([
                'success' => true,
                'message' => 'Library rebuild',
            ]);

        $this->assertDatabaseHas('h5p_libraries_libraries', [
            'library_id' => $library->id,
            'required_library_id' => $preLib->id,
            'dependency_type' => 'preloaded',
        ]);
        $this->assertDatabaseHas('h5p_libraries_libraries', [
            'library_id' => $library->id,
            'required_library_id' => $dynLib->id,
            'dependency_type' => 'dynamic',
        ]);
        $this->assertDatabaseHas('h5p_libraries_libraries', [
            'library_id' => $library->id,
            'required_library_id' => $edLib->id,
            'dependency_type' => 'editor',
        ]);
    }
}
