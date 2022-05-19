<?php

declare(strict_types=1);

namespace Tests\Unit\Libraries\H5P;

use App\Libraries\H5P\H5pPresave;
use Generator;
use Illuminate\Filesystem\FilesystemAdapter;
use InvalidArgumentException;
use League\Flysystem\Adapter\Local;
use League\Flysystem\Filesystem;
use PHPUnit\Framework\TestCase;

final class H5pPresaveTest extends TestCase
{
    private H5pPresave $presave;

    protected function setUp(): void
    {
        $this->presave = new H5pPresave(
            new FilesystemAdapter(
                new Filesystem(new Local(__DIR__.'/Stub/Presave'), [
                    'url' => '/test'
                ]),
            ),
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

    /**
     * @dataProvider getBadLibraries
     */
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

    /**
     * @dataProvider getBadLibraries
     */
    public function testCannotGetContentsOfNonExistentScripts(string $library): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->presave->getScriptContents($library);
    }

    public function getBadLibraries(): Generator
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
