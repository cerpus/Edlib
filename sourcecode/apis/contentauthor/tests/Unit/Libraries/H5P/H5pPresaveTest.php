<?php

declare(strict_types=1);

namespace Tests\Unit\Libraries\H5P;

use App\Libraries\H5P\H5pPresave;
use Generator;
use Illuminate\Filesystem\FilesystemAdapter;
use InvalidArgumentException;
use League\Flysystem\Filesystem;
use League\Flysystem\Local\LocalFilesystemAdapter;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class H5pPresaveTest extends TestCase
{
    private H5pPresave $presave;

    protected function setUp(): void
    {
        $adapter = new LocalFilesystemAdapter(__DIR__ . '/Stub/Presave');

        $this->presave = new H5pPresave(
            new FilesystemAdapter(new Filesystem($adapter), $adapter, [
                'url' => '/test',
            ]),
        );
    }

    public function testHasExistingScript(): void
    {
        $this->assertTrue($this->presave->hasScript('H5P.Foo'));
    }

    public function testDoesNotHaveNonExistentScript(): void
    {
        $this->assertFalse($this->presave->hasScript('H5P.Bar'));
    }

    public function testGetScriptUrl(): void
    {
        $this->assertSame(
            '/test/H5P.Foo/presave.js',
            $this->presave->getScriptUrl('H5P.Foo'),
        );
    }

    #[DataProvider('getBadLibraries')]
    public function testCannotGetUrlOfNonExistentScript(string $library): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->presave->getScriptUrl($library);
    }

    public function testGetScriptContents(): void
    {
        $this->assertSame(
            "// dummy file\n",
            $this->presave->getScriptContents('H5P.Foo'),
        );
    }

    #[DataProvider('getBadLibraries')]
    public function testCannotGetContentsOfNonExistentScripts(string $library): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->presave->getScriptContents($library);
    }

    public static function getBadLibraries(): Generator
    {
        yield 'non-existent script directory' => ['H5P.Bar'];
        yield 'existing directory, but no presave.js' => ['BadLibrary'];
    }

    public function testGetAllLibrariesWithScripts(): void
    {
        $this->assertSame(
            ['H5P.Foo'],
            $this->presave->getAllLibrariesWithScripts(),
        );
    }
}
