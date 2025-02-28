<?php

namespace Tests\Integration\Http\Controllers\Admin;

use App\ContentVersion;
use App\H5PContent;
use App\H5PLibrary;
use App\H5PLibraryLanguage;
use App\H5PLibraryLibrary;
use App\Http\Controllers\Admin\AdminH5PDetailsController;
use App\Libraries\ContentAuthorStorage;
use App\Libraries\H5P\Framework;
use Exception;
use Generator;
use Illuminate\Auth\GenericUser;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\UploadedFile;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class AdminH5PDetailsControllerTest extends TestCase
{
    use WithFaker;
    use RefreshDatabase;

    public function test_checkLibrary(): void
    {
        $library = H5PLibrary::factory()->create();
        $libLang = H5PLibraryLanguage::factory(3)->create([
            'library_id' => $library->id,
        ]);
        $libraryDep = H5PLibrary::factory()->create([
            'name' => 'H5P.EditorDep',
            'major_version' => 2,
            'minor_version' => 3,
        ]);
        $libraryPre = H5PLibrary::factory()->create([
            'name' => 'H5P.PreDep',
            'major_version' => 5,
            'minor_version' => 6,
        ]);
        $libraryDepX = H5PLibrary::factory()->create([
            'name' => 'H5P.EditorDepX',
            'major_version' => 2,
            'minor_version' => 3,
        ]);
        $libraryPreX = H5PLibrary::factory()->create([
            'name' => 'H5P.PreDepX',
            'major_version' => 5,
            'minor_version' => 6,
        ]);
        H5PLibrary::factory()->create([
            'name' => 'H5P.NotLinked',
            'major_version' => 3,
            'minor_version' => 4,
        ]);
        H5PLibraryLibrary::create([
            'library_id' => $library->id,
            'required_library_id' => $libraryDep->id,
            'dependency_type' => 'editor',
        ]);
        H5PLibraryLibrary::create([
            'library_id' => $library->id,
            'required_library_id' => $libraryPre->id,
            'dependency_type' => 'preloaded',
        ]);
        H5PLibraryLibrary::create([
            'library_id' => $library->id,
            'required_library_id' => $libraryDepX->id,
            'dependency_type' => 'editor',
        ]);
        H5PLibraryLibrary::create([
            'library_id' => $library->id,
            'required_library_id' => $libraryPreX->id,
            'dependency_type' => 'preloaded',
        ]);

        $storage = $this->createMock(ContentAuthorStorage::class);
        $this->instance(ContentAuthorStorage::class, $storage);
        $storage
            ->expects($this->once())
            ->method('copyFolder')
            ->with(
                $this->isInstanceOf(FilesystemAdapter::class),
                $this->isInstanceOf(FilesystemAdapter::class),
                $this->equalTo('libraries/H5P.Foobar-1.2'),
                $this->equalTo('libraries/H5P.Foobar-1.2'),
                $this->equalTo([]),
            );

        $framework = $this->createMock(Framework::class);
        $this->instance(Framework::class, $framework);
        $invokedCount = $this->exactly(2);
        $framework
            ->expects($invokedCount)
            ->method('getMessages')
            ->willReturnCallback(function ($params) use ($invokedCount) {
                match ($invokedCount->numberOfInvocations()) {
                    1 => $this->assertSame('info', $params),
                    2 => $this->assertSame('error', $params),
                    default => throw new Exception('Mocked function "getMessages" called too many times'),
                };

                return [];
            });

        $validator = $this->createMock(\H5PValidator::class);
        $this->instance(\H5PValidator::class, $validator);
        $validator->h5pF = $framework;
        $validator
            ->expects($this->once())
            ->method('getLibraryData')
            ->with($this->equalTo('H5P.Foobar-1.2'), '/tmp/h5p/libraries/H5P.Foobar-1.2', '/tmp/h5p/libraries')
            ->willReturn([
                'editorDependencies' => [
                    [
                        'machineName' => 'H5P.EditorDep',
                        'majorVersion' => 2,
                        'minorVersion' => 3,
                    ],
                    [
                        'machineName' => 'H5P.Missing',
                        'majorVersion' => 4,
                        'minorVersion' => 5,
                    ],
                ],
                'preloadedDependencies' => [
                    [
                        'machineName' => 'H5P.PreDep',
                        'majorVersion' => 5,
                        'minorVersion' => 6,
                    ],
                    [
                        'machineName' => 'H5P.NotLinked',
                        'majorVersion' => 3,
                        'minorVersion' => 4,
                    ],
                ],
            ]);

        $controller = app(AdminH5PDetailsController::class);
        $res = $controller->checkLibrary(H5PLibrary::find($library->id));

        $data = $res->getData();

        $this->assertArrayHasKey('library', $data);
        $this->assertArrayHasKey('libData', $data);
        $this->assertArrayHasKey('preloadDeps', $data);
        $this->assertArrayHasKey('editorDeps', $data);
        $this->assertArrayHasKey('usedBy', $data);
        $this->assertArrayHasKey('info', $data);
        $this->assertArrayHasKey('error', $data);
        $this->assertArrayHasKey('languages', $data);

        $this->assertCount(2, $data['libData']['editorDependencies']);
        $this->assertCount(2, $data['libData']['preloadedDependencies']);
        $this->assertEquals('H5P.EditorDep', $data['libData']['editorDependencies'][0]['machineName']);
        $this->assertTrue($data['libData']['editorDependencies'][0]['dependencySet']);
        $this->assertEquals('H5P.Missing', $data['libData']['editorDependencies'][1]['machineName']);
        $this->assertFalse($data['libData']['editorDependencies'][1]['dependencySet']);

        $this->assertEquals('H5P.PreDep', $data['libData']['preloadedDependencies'][0]['machineName']);
        $this->assertTrue($data['libData']['preloadedDependencies'][0]['dependencySet']);
        $this->assertEquals('H5P.NotLinked', $data['libData']['preloadedDependencies'][1]['machineName']);
        $this->assertFalse($data['libData']['preloadedDependencies'][1]['dependencySet']);

        $this->assertCount(1, $data['preloadDeps']);
        $this->assertCount(1, $data['editorDeps']);

        $this->assertEquals('H5P.PreDepX', $data['preloadDeps']->first()->requiredLibrary->name);
        $this->assertEquals('H5P.EditorDepX', $data['editorDeps']->first()->requiredLibrary->name);

        $this->assertCount(3, $data['languages']);
        $libLang->pluck('language_code')->each(
            fn($langCode) => $this->assertContains($langCode, $data['languages']),
        );
    }

    public function test_contentForLibrary(): void
    {
        $user = new GenericUser([
            'roles' => ['superadmin'],
            'name' => 'Super Tester',
        ]);

        $library = H5PLibrary::factory()->create();
        $failedContent = H5PContent::factory()->create([
            'version_id' => $this->faker->uuid,
            'library_id' => $library->id,
            'updated_at' => Carbon::now()->sub('1d'),
        ]);
        $versionContent = H5PContent::factory()->create([
            'version_id' => $this->faker->uuid,
            'library_id' => $library->id,
            'updated_at' => Carbon::now(),
        ]);
        $versionId = $versionContent->version_id;

        ContentVersion::factory()->create([
            'id' => $versionId,
            'content_id' => $versionContent->id,
        ]);

        $res = $this->withSession(['user' => $user])
            ->get(route('admin.content-library', $library))
            ->assertOk()
            ->original;

        $data = $res->getData();

        $this->assertArrayHasKey('library', $data);
        $this->assertArrayHasKey('paginator', $data);
        $this->assertArrayHasKey('listAll', $data);
        $this->assertArrayHasKey('latestCount', $data);

        $this->assertFalse($data['listAll']);
        $this->assertSame(1, $data['latestCount']);
        $this->assertInstanceOf(LengthAwarePaginator::class, $data['paginator']);
        $this->assertSame(2, $data['paginator']->count());

        $item = $data['paginator']->getCollection()->first();
        $this->assertSame($versionContent->id, $item['item']->id);
        $this->assertTrue($item['isLatest']);
        $this->assertSame($data['library']->id, $item['item']->library_id);

        $item = $data['paginator']->getCollection()->last();
        $this->assertSame($failedContent->id, $item['item']->id);
        $this->assertNull($item['isLatest']);
        $this->assertSame($data['library']->id, $item['item']->library_id);
    }

    public function test_contentHistory(): void
    {
        $user = new GenericUser([
            'roles' => ['superadmin'],
            'name' => 'Super Tester',
        ]);
        $f4mId = $this->faker->uuid;
        $library = H5PLibrary::factory()->create();
        $parent = H5PContent::factory()->create([
            'version_id' => $this->faker->uuid,
            'library_id' => $library->id,
        ]);
        $content = H5PContent::factory()->create([
            'id' => 42,
            'version_id' => $this->faker->uuid,
            'library_id' => $library->id,
        ]);
        $child = H5PContent::factory()->create([
            'version_id' => $this->faker->uuid,
            'library_id' => $library->id,
        ]);

        $parentVersion = ContentVersion::factory()->create([
            'id' => $parent->version_id,
            'content_id' => $parent->id,
        ]);
        $version = ContentVersion::factory()->create([
            'id' => $content->version_id,
            'content_id' => $content->id,
            'parent_id' => $parentVersion->id,
        ]);
        $childVersion = ContentVersion::factory()->create([
            'id' => $child->version_id,
            'content_id' => $child->id,
            'parent_id' => $version->id,
        ]);

        $response = $this->withSession(['user' => $user])
            ->get(route('admin.content-details', $content))
            ->assertOk()
            ->original;

        $data = $response->getData();

        $this->assertArrayHasKey('content', $data);
        $this->assertArrayHasKey('requestedVersion', $data);
        $this->assertArrayHasKey('history', $data);

        $this->assertNull($data['requestedVersion']);
        $this->assertSame($content->id, $data['content']->id);

        $this->assertInstanceOf(Collection::class, $data['history']);
        $this->assertCount(3, $data['history']);
        $this->assertArrayHasKey($parentVersion->id, $data['history']);
        $this->assertArrayHasKey($version->id, $data['history']);
        $this->assertArrayHasKey($childVersion->id, $data['history']);

        $history = $data['history']->get($version->id);
        $this->assertIsArray($history);
        $this->assertEquals($content->id, $history['content_id']);
        $this->assertEquals($parent->version_id, $history['parent']);
        $this->assertCount(1, $history['children']);
        $this->assertEquals($child->version_id, $history['children'][0]['id']);
        $this->assertEquals($child->id, $history['children'][0]['content_id']);
    }

    public function test_contentHistory_noResource(): void
    {
        $user = new GenericUser([
            'roles' => ['superadmin'],
            'name' => 'Super Tester',
        ]);
        $library = H5PLibrary::factory()->create();
        $content = H5PContent::factory()->create([
            'id' => 42,
            'version_id' => $this->faker->uuid,
            'library_id' => $library->id,
        ]);

        $response = $this->withSession(['user' => $user])
            ->get(route('admin.content-details', $content))
            ->assertOk()
            ->original;

        $data = $response->getData();

        $this->assertSame($content->id, $data['content']['id']);
        $this->assertNull($data['requestedVersion']);
        $this->assertCount(0, $data['history']);
    }

    public function test_libraryTranslation(): void
    {
        Storage::fake();
        $user = new GenericUser([
            'roles' => ['superadmin'],
            'name' => 'Super Tester',
        ]);
        $library = H5PLibrary::factory()->create();
        H5PLibraryLanguage::factory()->create(['library_id' => $library->id]);
        $translation = H5PLibraryLanguage::factory()->create([
            'library_id' => $library->id,
            'translation' => '{"data":"DB translation"}',
        ]);

        Storage::put(
            sprintf('libraries/%s/language/%s.json', $library->getFolderName(), $translation->language_code),
            '{"data":"File translation"}',
        );

        $response = $this->withSession(['user' => $user])
            ->get(route('admin.library-translation', [$library, $translation->language_code]))
            ->assertOk()
            ->original;

        $this->assertInstanceOf(View::class, $response);

        $data = $response->getData();
        $this->assertSame($library->id, $data['library']->id);
        $this->assertSame($translation->language_code, $data['languageCode']);
        $this->assertSame($translation->translation, $data['translationDb']);
        $this->assertSame('{"data":"File translation"}', $data['translationFile']);
    }

    public function test_libraryTranslation_UnknownCode(): void
    {
        Storage::fake();
        $user = new GenericUser([
            'roles' => ['superadmin'],
            'name' => 'Super Tester',
        ]);
        $library = H5PLibrary::factory()->create();
        $translation = H5PLibraryLanguage::factory()->create([
            'library_id' => $library->id,
            'language_code' => 'nb',
            'translation' => '{"data":"DB translation"}',
        ]);

        Storage::put(
            sprintf('libraries/%s/language/%s.json', $library->getFolderName(), $translation->language_code),
            '{"data":"File translation"}',
        );

        $response = $this->withSession(['user' => $user])
            ->get(route('admin.library-translation', [$library, 'nn']))
            ->assertOk()
            ->original;

        $this->assertInstanceOf(View::class, $response);

        $data = $response->getData();
        $this->assertSame($library->id, $data['library']->id);
        $this->assertSame('nn', $data['languageCode']);
        $this->assertNull($data['translationDb']);
        $this->assertNull($data['translationFile']);
    }

    public function test_libraryTranslationUpdate_Text(): void
    {
        Storage::fake();
        $user = new GenericUser([
            'roles' => ['superadmin'],
            'name' => 'Super Tester',
        ]);
        $library = H5PLibrary::factory()->create();
        $unchangedTranslation = H5PLibraryLanguage::factory()->create(['library_id' => $library->id]);
        $translation = H5PLibraryLanguage::factory()->create([
            'library_id' => $library->id,
            'translation' => '{"data":"DB translation"}',
        ]);
        $this->assertDatabaseCount('h5p_libraries_languages', 2);

        Storage::put(
            sprintf('libraries/%s/language/%s.json', $library->getFolderName(), $translation->language_code),
            '{"data":"File translation"}',
        );

        $response = $this->withSession(['user' => $user])
            ->post(
                route('admin.library-translation', [$library, $translation->language_code]),
                ['translation' => '{"data":"Updated DB translation"}'],
            )
            ->assertOk()
            ->original;

        $this->assertInstanceOf(View::class, $response);
        $this->assertDatabaseHas('h5p_libraries_languages', [
            'library_id' => $library->id,
            'language_code' => $translation->language_code,
            'translation' => '{"data":"Updated DB translation"}',
        ]);
        $this->assertDatabaseHas('h5p_libraries_languages', [
            'library_id' => $library->id,
            'language_code' => $unchangedTranslation->language_code,
            'translation' => $unchangedTranslation->translation,
        ]);

        $data = $response->getData();
        $this->assertSame($library->id, $data['library']->id);
        $this->assertSame($translation->language_code, $data['languageCode']);
        $this->assertSame('{"data":"Updated DB translation"}', $data['translationDb']);
        $this->assertSame('{"data":"File translation"}', $data['translationFile']);
        $this->assertTrue($data['messages']->isEmpty());
    }

    public function test_libraryTranslationUpdate_UnkownCode(): void
    {
        Storage::fake();
        $user = new GenericUser([
            'roles' => ['superadmin'],
            'name' => 'Super Tester',
        ]);
        $library = H5PLibrary::factory()->create();
        $translation = H5PLibraryLanguage::factory()->create([
            'library_id' => $library->id,
            'language_code' => 'nb',
            'translation' => '{"data":"DB translation"}',
        ]);

        Storage::put(
            sprintf('libraries/%s/language/%s.json', $library->getFolderName(), $translation->language_code),
            '{"data":"File translation"}',
        );

        $response = $this->withSession(['user' => $user])
            ->post(
                route('admin.library-translation', [$library, 'nn']),
                ['translation' => '{"data":"Updated DB translation"}'],
            )
            ->assertOk()
            ->original;

        $this->assertInstanceOf(View::class, $response);
        $this->assertDatabaseMissing('h5p_libraries_languages', [
            'library_id' => $library->id,
            'language_code' => 'nn',
        ]);

        $data = $response->getData();
        $this->assertSame($library->id, $data['library']->id);
        $this->assertSame('nn', $data['languageCode']);
        $this->assertNull($data['translationDb']);
        $this->assertNull($data['translationFile']);
        $this->assertContains('No rows was updated', $data['messages']);
    }

    #[DataProvider('provider_libraryTranslationUpdate_File')]
    public function test_libraryTranslationUpdate_FileError(string $fileContents, ?string $expectedMessage): void
    {
        Storage::fake();
        $user = new GenericUser([
            'roles' => ['superadmin'],
            'name' => 'Super Tester',
        ]);
        $library = H5PLibrary::factory()->create();
        $unchangedTranslation = H5PLibraryLanguage::factory()->create(['library_id' => $library->id]);
        $translation = H5PLibraryLanguage::factory()->create([
            'library_id' => $library->id,
            'translation' => '{"data":"DB translation"}',
        ]);

        Storage::put(
            sprintf('libraries/%s/language/%s.json', $library->getFolderName(), $translation->language_code),
            '{"data":"File translation"}',
        );

        $file = UploadedFile::fake()->createWithContent(
            $translation->language_code . '.json',
            $fileContents,
        );

        $response = $this->withSession(['user' => $user])
            ->post(
                route('admin.library-translation', [$library, $translation->language_code]),
                ['translationFile' => $file],
            )
            ->assertOk()
            ->original;

        $storedTranslation = $expectedMessage === null ? $fileContents : $translation->translation;
        $this->assertInstanceOf(View::class, $response);
        $this->assertDatabaseHas('h5p_libraries_languages', [
            'library_id' => $library->id,
            'language_code' => $translation->language_code,
            'translation' => $storedTranslation,
        ]);

        $this->assertDatabaseHas('h5p_libraries_languages', [
            'library_id' => $library->id,
            'language_code' => $unchangedTranslation->language_code,
            'translation' => $unchangedTranslation->translation,
        ]);

        $data = $response->getData();
        $this->assertSame($library->id, $data['library']->id);
        $this->assertSame($translation->language_code, $data['languageCode']);
        $this->assertSame($storedTranslation, $data['translationDb']);
        $this->assertSame('{"data":"File translation"}', $data['translationFile']);
        if ($expectedMessage !== null) {
            $this->assertContains($expectedMessage, $data['messages']);
        } else {
            $this->assertTrue($data['messages']->isEmpty());
        }
    }

    public static function provider_libraryTranslationUpdate_File(): Generator
    {
        yield 'valid file' => ['{"data":"Upload translation"}', null];
        yield 'empty file' => ['', 'Content was empty'];
        yield 'invalid file' => ['Not JSON', 'Syntax error'];
    }
}
