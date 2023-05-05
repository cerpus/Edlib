<?php

declare(strict_types=1);

namespace Tests\Unit\Libraries\H5P;

use App\H5PLibrary;
use App\H5PLibraryLibrary;
use App\Libraries\H5P\Framework;
use ArrayObject;
use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\TransferException;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Response;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use InvalidArgumentException;
use PDO;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Tests\TestCase;

final class FrameworkTest extends TestCase
{
    use RefreshDatabase;

    /** @var ArrayObject<int, array{request: RequestInterface, response: ResponseInterface}> */
    private ArrayObject $history;

    private Framework $framework;

    private MockHandler $mockedResponses;

    protected function setUp(): void
    {
        parent::setUp();

        $this->history = new ArrayObject();
        $this->mockedResponses = new MockHandler();

        $handler = HandlerStack::create($this->mockedResponses);
        $handler->push(Middleware::history($this->history));

        $client = new Client(['handler' => $handler]);

        $this->framework = new Framework(
            $client,
            $this->createMock(PDO::class),
            $this->createMock(Filesystem::class),
        );
    }

    public function testFetchExternalData(): void
    {
        $this->mockedResponses->append(new Response(200, [], 'Some body'));

        $this->assertSame(
            'Some body',
            $this->framework->fetchExternalData('http://www.example.com')
        );
    }

    public function testFetchExternalDataNonBlocking(): void
    {
        $this->mockedResponses->append(new Response(200, [], 'Some body'));

        $data = $this->framework->fetchExternalData(
            'http://www.example.com',
            blocking: false,
        );

        $this->assertNull($data);
        $this->assertSame(
            'http://www.example.com',
            (string) $this->history[0]['request']->getUri(),
        );
        $this->assertSame(0, $this->history[0]['response']->getBody()->tell());
    }

    public function testFetchExternalDataWithData(): void
    {
        $this->mockedResponses->append(new Response(200, [], 'Some body'));

        $this->framework->fetchExternalData('http://www.example.com', [
            'foo' => 'bar',
        ]);

        $this->assertSame(
            'foo=bar',
            $this->history[0]['request']->getBody()->getContents(),
        );
    }

    public function testFetchExternalDataWithFullData(): void
    {
        $this->mockedResponses->append(new Response(200, [], 'Some body'));

        $response = $this->framework->fetchExternalData(
            'http://www.example.com',
            [
                'foo' => 'bar',
            ],
            fullData: true,
        );

        $this->assertSame(
            'foo=bar',
            $this->history[0]['request']->getBody()->getContents(),
        );
        $this->assertIsArray($response);
        $this->assertArrayHasKey('status', $response);
        $this->assertArrayHasKey('headers', $response);
        $this->assertArrayHasKey('data', $response);
        $this->assertSame(200, $response['status']);
        $this->assertSame('Some body', $response['data']);
    }

    public function testFetchExternalDataWithGuzzleError(): void
    {
        $this->mockedResponses->append(new TransferException());

        $this->assertNull(
            $this->framework->fetchExternalData('http://www.example.com'),
        );
    }

    public function testFetchExternalDataWithOtherException(): void
    {
        $e = new Exception('oops');
        $this->mockedResponses->append($e);

        $this->expectExceptionObject($e);

        $this->framework->fetchExternalData('http://www.example.com');
    }

    public function testGetInfoMessages(): void
    {
        $this->assertSame([], $this->framework->getMessages('info'));
    }

    public function testAddInfoMessage(): void
    {
        $this->framework->setInfoMessage('this is some info');
        $this->framework->setInfoMessage('this is more info');
        $this->framework->setErrorMessage('this is not info');

        $this->assertSame([
            'this is some info',
            'this is more info',
        ], $this->framework->getMessages('info'));
    }

    public function testGetErrorMessages(): void
    {
        $this->assertSame([], $this->framework->getMessages('error'));
    }

    public function testAddErrorMessage(): void
    {
        $this->framework->setErrorMessage('this is an error');
        $this->framework->setErrorMessage('this is another error');
        $this->framework->setInfoMessage('this is not an error');

        $this->assertSame([
            'this is an error',
            'this is another error',
        ], $this->framework->getMessages('error'));
    }

    public function testGetMessagesOfUnknownType(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->framework->getMessages('unknown');
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
            'metadataSettings' => 'Yupp',
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

        /** @var H5PLibrary $library */
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
        /** @var H5PLibrary $editDep */
        $editDep = H5PLibrary::factory()->create([
            'name' => 'H5PEditor.Foobar',
            'patch_version_in_folder_name' => true,
        ]);
        /** @var H5PLibrary $saved */
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
}
