<?php

namespace Tests\Integration;

use App\H5PContent;
use App\H5PLibrary;
use App\Libraries\DataObjects\ContentTypeDataObject;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class H5PContentTest extends TestCase
{
    use RefreshDatabase;

    /** @dataProvider provider_getContentTypeInfo */
    public function test_getContentTypeInfo_WithIcon($usePatch, $expectedPath): void
    {
        $library = H5PLibrary::factory()->create([
            'has_icon' => 1,
            'patch_version_in_folder_name' => $usePatch,
        ]);

        $frameWork = $this->createMock(\H5PFrameworkInterface::class);
        $this->instance(\H5PFrameworkInterface::class, $frameWork);

        $frameWork
            ->expects($this->once())
            ->method('getLibraryFileUrl')
            ->with($expectedPath, 'icon.svg')
            ->willReturn("assets/$expectedPath/icon.svg");

        $result = H5PContent::getContentTypeInfo('H5P.Foobar');
        $this->assertInstanceOf(ContentTypeDataObject::class, $result);
        $this->assertSame('H5P.Foobar', $result->contentType);
        $this->assertSame($library->title, $result->title);
        $this->assertSame("assets/$expectedPath/icon.svg", $result->icon);
    }

    public function provider_getContentTypeInfo(): \Generator
    {
        yield [false, 'H5P.Foobar-1.2'];
        yield [true, 'H5P.Foobar-1.2.3'];
    }

    public function test_getContentTypeInfo_NoIcon(): void
    {
        $library = H5PLibrary::factory()->create();

        $frameWork = $this->createMock(\H5PFrameworkInterface::class);
        $this->instance(\H5PFrameworkInterface::class, $frameWork);

        $frameWork
            ->expects($this->never())
            ->method('getLibraryFileUrl');

        $result = H5PContent::getContentTypeInfo('H5P.Foobar');
        $this->assertInstanceOf(ContentTypeDataObject::class, $result);
        $this->assertSame('H5P.Foobar', $result->contentType);
        $this->assertSame($library->title, $result->title);
        $this->assertSame('http://localhost/graphical/h5p_logo.svg', $result->icon);
    }
}
