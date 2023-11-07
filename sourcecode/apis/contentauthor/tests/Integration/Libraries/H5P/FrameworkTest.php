<?php

declare(strict_types=1);

namespace Tests\Integration\Libraries\H5P;

use App\H5PContent;
use App\H5PLibrary;
use App\H5PLibraryLibrary;
use App\Libraries\ContentAuthorStorage;
use App\Libraries\H5P\Framework;
use ArrayObject;
use Generator;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Storage;
use PDO;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Tests\TestCase;

final class FrameworkTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    /** @var ArrayObject<int, array{request: RequestInterface, response: ResponseInterface}> */
    private ArrayObject $history;

    private Framework $framework;

    protected function setUp(): void
    {
        parent::setUp();

        $this->history = new ArrayObject();
        $mockedResponses = new MockHandler();

        $handler = HandlerStack::create($mockedResponses);
        $handler->push(Middleware::history($this->history));

        $client = new Client(['handler' => $handler]);

        $this->framework = new Framework(
            $client,
            $this->createMock(PDO::class),
            $this->createMock(Filesystem::class),
        );
    }

    public function testSaveLibrary(): void
    {
        $input = [
            'machineName' => 'H5P.UnitTest',
            'title' => 'Unit Test',
            'majorVersion' => 2,
            'minorVersion' => 4,
            'patchVersion' => 6,
            'runnable' => 1,
            'metadataSettings' => '{"disable":false,"disableExtraTitleField":true}',
            'addTo' => ['machineName' => 'Something'],
            'hasIcon' => 1,
            'embedTypes' => ['E1', 'E2'],
            'preloadedJs' => [
                ['path' => 'PJ1', 'name' => 'PJ1 name', 'machineName' => 'H5P.Pj1'],
                ['path' => 'PJ2', 'name' => 'PJ2 name', 'machineName' => 'H5P.Pj2'],
            ],
            'preloadedCss' => [
                ['path' => 'PC1', 'name' => 'PC1 name', 'machineName' => 'H5P.Pc1'],
                ['path' => 'PC2', 'name' => 'PC2 name', 'machineName' => 'H5P.Pc1'],
            ],
            'dropLibraryCss' => [
                ['path' => 'DC1', 'name' => 'DC1 name', 'machineName' => 'H5P.Dc1'],
                ['path' => 'DC2', 'name' => 'DC2 name', 'machineName' => 'H5P.Dc2'],
            ],
            'language' => [
                'nb' => 'Norsk Bokmål',
                'nn' => 'Norsk Nynorsk',
            ],
        ];
        $this->framework->saveLibraryData($input);

        $this->assertDatabaseHas('h5p_libraries', ['id' => $input['libraryId']]);
        $this->assertDatabaseHas('h5p_libraries_languages', [
            'library_id' => $input['libraryId'],
            'language_code' => 'nb',
            'translation' => 'Norsk Bokmål',
        ]);
        $this->assertDatabaseHas('h5p_libraries_languages', [
            'library_id' => $input['libraryId'],
            'language_code' => 'nn',
            'translation' => 'Norsk Nynorsk',
        ]);

        $library = H5PLibrary::find($input['libraryId']);

        $this->assertSame('H5P.UnitTest', $library->name);
        $this->assertSame('Unit Test', $library->title);
        $this->assertSame(2, $library->major_version);
        $this->assertSame(4, $library->minor_version);
        $this->assertSame(6, $library->patch_version);
        $this->assertSame(1, $library->runnable);
        $this->assertSame(0, $library->fullscreen);
        $this->assertSame('E1, E2', $library->embed_types);
        $this->assertSame('PJ1, PJ2', $library->preloaded_js);
        $this->assertSame('PC1, PC2', $library->preloaded_css);
        $this->assertSame('H5P.Dc1, H5P.Dc2', $library->drop_library_css);
        $this->assertSame('', $library->semantics);
        $this->assertSame(1, $library->has_icon);
        $this->assertSame(true, $library->patch_version_in_folder_name);
        $metadata = json_decode($library->metadata_settings, flags: JSON_THROW_ON_ERROR);
        $this->assertFalse($metadata->disable);
        $this->assertTrue($metadata->disableExtraTitleField);
    }

    public function testLoadLibrary(): void
    {
        H5PLibrary::factory()->create([
            'major_version' => 1,
            'minor_version' => 1,
            'patch_version' => 9,
        ]);
        H5PLibrary::factory()->create([
            'major_version' => 1,
            'minor_version' => 2,
            'patch_version' => 2,
        ]);
        $editDep = H5PLibrary::factory()->create([
            'name' => 'H5PEditor.Foobar',
            'patch_version_in_folder_name' => true,
        ]);
        $saved = H5PLibrary::factory()->create([
            'patch_version_in_folder_name' => true,
        ]);
        H5PLibraryLibrary::create([
            'library_id' => $saved->id,
            'required_library_id' => $editDep->id,
            'dependency_type' => 'editor',
        ]);

        $library = $this->framework->loadLibrary('H5P.Foobar', 1, 2);
        $this->assertSame($saved->id, $library['libraryId']);
        $this->assertSame($saved->name, $library['machineName']);
        $this->assertSame($saved->major_version, $library['majorVersion']);
        $this->assertSame($saved->minor_version, $library['minorVersion']);
        $this->assertSame($saved->patch_version, $library['patchVersion']);
        $this->assertSame($saved->patch_version_in_folder_name, $library['patchVersionInFolderName']);

        $this->assertSame($editDep->name, $library['editorDependencies'][0]['machineName']);
        $this->assertSame($editDep->patch_version_in_folder_name, $library['editorDependencies'][0]['patchVersionInFolderName']);
    }

    /** @dataProvider provider_usePatch */
    public function test_deleteLibrary($usePatch): void
    {
        $disk = Storage::fake();
        $caStorage = App(ContentAuthorStorage::class);
        $tmpDisk = Storage::fake($caStorage->getH5pTmpDiskName());

        $library = H5PLibrary::factory()->create(['patch_version_in_folder_name' => $usePatch]);
        $path = 'libraries/' . $library->getFolderName();

        $this->assertFalse($disk->exists($path));
        $disk->put($path . '/library.json', 'just testing');
        $this->assertTrue($disk->exists($path . '/library.json'));

        $this->assertFalse($tmpDisk->exists($path));
        $tmpDisk->put($path . '/library.json', 'just testing');
        $this->assertTrue($tmpDisk->exists($path . '/library.json'));

        $lib = ['id' => $library->id];
        $this->assertDatabaseHas('h5p_libraries', $lib);
        $this->framework->deleteLibrary((object) $lib);
        $this->assertDatabaseMissing('h5p_libraries', $lib);

        $this->assertFalse($disk->exists($path));
        $this->assertFalse($tmpDisk->exists($path));
    }

    /** @dataProvider provider_usePatch */
    public function test_loadContent($usePatch): void
    {
        $h5pLibrary = H5PLibrary::factory()->create(['patch_version_in_folder_name' => $usePatch]);
        $h5pContent = H5PContent::factory()->create(['library_id' => $h5pLibrary->id]);

        $content = $this->framework->loadContent($h5pContent->id);

        $this->assertSame($h5pContent->id, $content['id']);
        $this->assertSame($h5pContent->id, $content['contentId']);
        $this->assertSame($h5pLibrary->id, $content['libraryId']);
        $this->assertSame($h5pLibrary->getLibraryString(), $content['libraryFullVersionName']);
    }

    public function provider_usePatch(): Generator
    {
        yield [false];
        yield [true];
    }

    /** @dataProvider provider_isPatchedLibrary */
    public function test_isPatchedLibrary(int $patchVersion, bool $expected)
    {
        $library = H5PLibrary::factory()->create();

        $this->assertSame($expected, $this->framework->isPatchedLibrary([
            'machineName' => $library->name,
            'majorVersion' => $library->major_version,
            'minorVersion' => $library->minor_version,
            'patchVersion' => $patchVersion,
        ]));
    }

    public function provider_isPatchedLibrary(): Generator
    {
        yield 'same patch' => [3, false];
        yield 'older patch' => [2, false];
        yield 'newer patch' => [4, true];
    }

    public function test_insertContent(): void
    {
        $library = H5PLibrary::factory()->create();
        $input = [
            'title' => 'Some title',
            'params' => '{"data":"empty"}',
            'embed_type' => 'div',
            'disable' => false,
            'slug' => 'slugger',
            'user_id' => $this->faker->uuid,
            'max_score' => 42,
            'is_published' => false,
            'is_private' => false,
            'is_draft' => false,
            'language_iso_639_3' => 'nob',
            'library' => [
                'libraryId' => $library->id,
            ],
            'metadata' => [
                'title' => 'Some title',
                'license' => 'CC BY-NC',
            ],
        ];

        $contentId = $this->framework->insertContent($input);

        $this->assertDatabaseHas('h5p_contents', ['id' => $contentId]);
        $this->assertDatabaseHas('h5p_contents_metadata', ['content_id' => $contentId]);

        $content = H5PContent::find($contentId);
        $this->assertSame($input['title'], $content->title);
        $this->assertSame($input['params'], $content->parameters);
        $this->assertSame($input['library']['libraryId'], $content->library->id);
        $this->assertSame($input['embed_type'], $content->embed_type);
        $this->assertSame($input['max_score'], $content->max_score);
        $this->assertSame($input['slug'], $content->slug);
        $this->assertSame($input['is_published'], $content->is_published);
        $this->assertSame($input['is_draft'], $content->is_draft);

        $this->assertSame($input['metadata']['license'], $content->metadata->license);
    }

    /** @dataProvider provider_isContentSlugAvailable */
    public function test_isContentSlugAvailable(string $slug, bool $expected): void
    {
        H5PContent::factory()->create([
            'slug' => 'taken',
        ]);

        $this->assertSame($expected, $this->framework->isContentSlugAvailable($slug));
    }

    public function provider_isContentSlugAvailable(): Generator
    {
        yield 'unavailable' => ['taken', false];
        yield 'available' => ['available', true];
    }

    public function test_getLibraryContentCount(): void
    {
        $nr = H5PLibrary::factory()->create([
            'name' => 'H5P.NotRunnable',
            'runnable' => false,
        ]);
        H5PContent::factory(2)->create([
            'library_id' => $nr->id,
        ]);

        H5PLibrary::factory()->create([
            'name' => 'H5P.NoContent',
            'runnable' => true,
        ]);

        $library = H5PLibrary::factory()->create();
        H5PContent::factory(3)->create([
            'library_id' => $library->id,
        ]);

        $result = $this->framework->getLibraryContentCount();
        $this->assertCount(1, $result);
        $this->assertArrayHasKey('H5P.Foobar 1.2', $result);
        $this->assertSame(3, $result['H5P.Foobar 1.2']);
    }
}
