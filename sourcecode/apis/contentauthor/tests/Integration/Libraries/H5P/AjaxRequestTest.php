<?php

namespace Tests\Integration\Libraries\H5P;

use App\H5PLibrariesHubCache;
use App\H5PLibrary;
use App\H5PLibraryCapability;
use App\Libraries\H5P\AjaxRequest;
use Generator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Storage;
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

    /** @dataProvider provider_contentTypeCache_icon */
    public function test_contentTypeCache_icon(array $libraryData, string $iconPath): void
    {
        Storage::fake('test');

        $library = H5PLibrary::factory()->create($libraryData);

        H5PLibraryCapability::factory()->create([
            'library_id' => $library->id,
        ]);

        if ($iconPath !== '') {
            Storage::put($iconPath, 'icon content');
        }

        $content = $this
            ->post('/ajax', ['action' => \H5PEditorEndpoints::CONTENT_TYPE_CACHE, 'h5p_id' => ''])
            ->assertOk()
            ->assertJsonStructure([
                'outdated',
                'libraries',
                'recentlyUsed',
                'apiVersion' => [
                    'major',
                    'minor',
                ],
                'details',
            ])
            ->decodeResponseJson();

        $libraries = $content['libraries'];
        $this->assertCount(1, $libraries);
        $libData = $libraries[0];

        $this->assertSame($library->name, $libData['machineName']);

        if ($library->has_icon) {
            $this->assertStringContainsString($iconPath, $libData['icon']);
        } else {
            $this->assertArrayNotHasKey('icon', $libData);
        }
    }

    public function provider_contentTypeCache_icon(): Generator
    {
        yield 'No patch with icon' => [
            [
                'has_icon' => true,
                'patch_version_in_folder_name' => false,
            ],
            'libraries/H5P.Foobar-1.2/icon.svg',
        ];

        yield 'Patch with icon' => [
            [
                'has_icon' => true,
                'patch_version_in_folder_name' => true,
            ],
            'libraries/H5P.Foobar-1.2.3/icon.svg',
        ];

        yield 'Missing icon' => [
            [
                'has_icon' => true,
                'patch_version_in_folder_name' => true,
            ],
            '',
        ];

        yield 'No icon' => [
            [
                'has_icon' => false,
                'patch_version_in_folder_name' => true,
            ],
            'libraries/H5P.Foobar-1.2.3/icon.svg',
        ];
    }

    /** @dataProvider provider_contentTypeCache_LocalAndCache */
    public function test_contentTypeCache_localAndCache(bool $usePatchVersion): void
    {
        Storage::fake('test');

        $localOnlyLibrary = H5PLibrary::factory()->create([
            'name' => 'H5P.Snafu',
            'has_icon' => true,
            'patch_version_in_folder_name' => $usePatchVersion,
        ]);
        H5PLibraryCapability::factory()->create([
            'library_id' => $localOnlyLibrary->id,
        ]);

        $library = H5PLibrary::factory()->create([
            'patch_version_in_folder_name' => $usePatchVersion,
        ]);
        H5PLibraryCapability::factory()->create([
            'library_id' => $library->id,
        ]);

        $hubLibrary = H5PLibrariesHubCache::factory()->create();

        $content = $this
            ->withSession(['isAdmin' => true])
            ->post('/ajax', ['action' => \H5PEditorEndpoints::CONTENT_TYPE_CACHE, 'h5p_id' => ''])
            ->assertOk()
            ->assertJsonStructure([
                'outdated',
                'libraries',
                'recentlyUsed',
                'apiVersion' => [
                    'major',
                    'minor',
                ],
                'details',
            ])
            ->decodeResponseJson();

        $this->assertCount(2, $content['libraries']);

        $libData = $content['libraries'][0];
        $this->assertSame($hubLibrary->id, $libData['id']);
        $this->assertSame($hubLibrary->title, $libData['title']);
        $this->assertSame($hubLibrary->major_version, $libData['majorVersion']);
        $this->assertSame($hubLibrary->minor_version, $libData['minorVersion']);
        $this->assertSame($hubLibrary->patch_version, $libData['patchVersion']);
        $this->assertSame($library->major_version, $libData['localMajorVersion']);
        $this->assertSame($library->minor_version, $libData['localMinorVersion']);
        $this->assertSame($library->patch_version, $libData['localPatchVersion']);
        $this->assertTrue($libData['installed']);
        $this->assertFalse($libData['isUpToDate']);
        $this->assertFalse($libData['restricted']);
        $this->assertFalse($libData['canInstall']);
        $this->assertArrayHasKey('summary', $libData);
        $this->assertArrayHasKey('isRecommended', $libData);
        $this->assertArrayHasKey('popularity', $libData);
        $this->assertArrayHasKey('screenshots', $libData);
        $this->assertArrayHasKey('license', $libData);

        $libData = $content['libraries'][1];
        $this->assertSame($localOnlyLibrary->id, $libData['id']);
        $this->assertSame($localOnlyLibrary->title, $libData['title']);
        $this->assertSame($localOnlyLibrary->major_version, $libData['majorVersion']);
        $this->assertSame($localOnlyLibrary->minor_version, $libData['minorVersion']);
        $this->assertSame($localOnlyLibrary->patch_version, $libData['patchVersion']);
        $this->assertSame($localOnlyLibrary->major_version, $libData['localMajorVersion']);
        $this->assertSame($localOnlyLibrary->minor_version, $libData['localMinorVersion']);
        $this->assertSame($localOnlyLibrary->patch_version, $libData['localPatchVersion']);
        $this->assertTrue($libData['installed']);
        $this->assertTrue($libData['isUpToDate']);
        $this->assertFalse($libData['restricted']);
        $this->assertFalse($libData['canInstall']);
        $this->assertArrayNotHasKey('summary', $libData);
        $this->assertArrayNotHasKey('isRecommended', $libData);
        $this->assertArrayNotHasKey('popularity', $libData);
        $this->assertArrayNotHasKey('screenshots', $libData);
        $this->assertArrayNotHasKey('license', $libData);
    }

    public function provider_contentTypeCache_localAndCache(): Generator
    {
        yield 'no patch version' => [false];
        yield 'patch version' => [true];
    }
}
