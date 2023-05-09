<?php

namespace Tests\Integration\Libraries\H5P;

use App\H5PLibrary;
use App\H5PLibraryLibrary;
use App\Libraries\ContentAuthorStorage;
use App\Libraries\H5P\AjaxRequest;
use App\Libraries\H5P\Framework;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AjaxRequestTest extends TestCase
{
    use RefreshDatabase;

    public function test_libraryRebuild(): void
    {
        $library = H5PLibrary::factory()->create();
        $preLib = H5PLibrary::factory()->create(['name' => 'player', 'major_version' => 3, 'minor_version' => 14]);
        $dynLib = H5PLibrary::factory()->create(['name' => 'H5P.Dynamic', 'major_version' => 2, 'minor_version' => 42, 'patch_version' => 3, 'patch_version_in_folder_name' => true]);
        $edLib = H5PLibrary::factory()->create(['name' => 'FontOk', 'major_version' => 1, 'minor_version' => 3]);

        H5PLibraryLibrary::create(['library_id' => $library->id, 'required_library_id' => $preLib->id, 'dependency_type' => 'preloaded']);
        H5PLibraryLibrary::create(['library_id' => $library->id, 'required_library_id' => $dynLib->id, 'dependency_type' => 'dynamic']);
        H5PLibraryLibrary::create(['library_id' => $library->id, 'required_library_id' => $edLib->id, 'dependency_type' => 'editor']);

        $storage = $this->createMock(ContentAuthorStorage::class);
        $this->instance(ContentAuthorStorage::class, $storage);
        $storage
            ->expects($this->exactly(4))
            ->method('copyFolder');

        $framework = $this->createMock(Framework::class);
        $this->instance(Framework::class, $framework);
        $framework->expects($this->exactly(4))
            ->method('deleteLibraryDependencies')
            ->withConsecutive([$library->id], [$preLib->id], [$dynLib->id], [$edLib->id]);
        $framework->expects($this->exactly(3))
            ->method('saveLibraryDependencies')
            ->withConsecutive(
                [$library->id, [['machineName' => 'player', 'majorVersion' => 3, 'minorVersion' => 14]], 'preloaded'],
                [$library->id, [['machineName' => 'H5P.Dynamic', 'majorVersion' => 2, 'minorVersion' => 42]], 'dynamic'],
                [$library->id, [['machineName' => 'FontOk', 'majorVersion' => 1, 'minorVersion' => 3]], 'editor'],
            );
        $framework
            ->expects($this->exactly(8))
            ->method('getH5pPath')
            ->withConsecutive(['libraries'], ['libraries/H5P.Foobar-1.2'])
            ->willReturnOnConsecutiveCalls('libraries', 'libraries/H5P.Foobar-1.2');

        $core = $this->createMock(\H5PCore::class);
        $this->instance(\H5PCore::class, $core);
        $core->h5pF = $framework;
        $core->expects($this->once())->method('mayUpdateLibraries')->willReturn(true);

        $validator = $this->createMock(\H5PValidator::class);
        $this->instance(\H5PValidator::class, $validator);
        $validator->h5pF = $framework;
        $validator
            ->expects($this->exactly(4))
            ->method('getLibraryData')
            ->withConsecutive(['H5P.Foobar-1.2'], ['player-3.14'], ['H5P.Dynamic-2.42.3'], ['FontOk-1.3'])
            ->willReturnOnConsecutiveCalls([
                'preloadedDependencies' => [['machineName' => 'player', 'majorVersion' => 3, 'minorVersion' => 14]],
                'dynamicDependencies' => [['machineName' => 'H5P.Dynamic', 'majorVersion' => 2, 'minorVersion' => 42]],
                'editorDependencies' => [['machineName' => 'FontOk', 'majorVersion' => 1, 'minorVersion' => 3]],
            ], [], [], []);

        $this
            ->post('/ajax', ['action' => AjaxRequest::LIBRARY_REBUILD, 'libraryId' => $library->id])
            ->assertOk()
            ->assertJson([
                'success' => true,
                'message' => 'Library rebuild',
            ]);
    }
}
