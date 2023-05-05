<?php

namespace Tests\Unit;

use App\H5PContent;
use App\H5PLibrary;
use App\Libraries\DataObjects\ContentTypeDataObject;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class H5PContentTest extends TestCase
{
    use RefreshDatabase;

    public function test_getContentTypeInfo_WithIcon(): void
    {
        /** @var H5PLibrary $library */
        $library = H5PLibrary::factory()->create(['has_icon' => 1]);

        $frameWork = $this->createMock(\H5PFrameworkInterface::class);
        $this->instance(\H5PFrameworkInterface::class, $frameWork);

        $frameWork
            ->expects($this->once())
            ->method('getLibraryFileUrl')
            ->with('H5P.Foobar-1.2', 'icon.svg')
            ->willReturn('assets/H5P.Foobar-1.2/icon.svg');

        $result = H5PContent::getContentTypeInfo('H5P.Foobar');
        $this->assertInstanceOf(ContentTypeDataObject::class, $result);
        $this->assertSame('H5P.Foobar', $result->contentType);
        $this->assertSame($library->title, $result->title);
        $this->assertSame('assets/H5P.Foobar-1.2/icon.svg', $result->icon);
    }

    public function test_getContentTypeInfo_PatchName(): void
    {
        /** @var H5PLibrary $library */
        $library = H5PLibrary::factory()->create([
            'patch_version_in_folder_name' => true,
            'has_icon' => 1,
        ]);

        $frameWork = $this->createMock(\H5PFrameworkInterface::class);
        $this->instance(\H5PFrameworkInterface::class, $frameWork);

        $frameWork
            ->expects($this->once())
            ->method('getLibraryFileUrl')
            ->with('H5P.Foobar-1.2.3', 'icon.svg')
            ->willReturn('assets/H5P.Foobar-1.2.3/icon.svg');

        $result = H5PContent::getContentTypeInfo('H5P.Foobar');
        $this->assertInstanceOf(ContentTypeDataObject::class, $result);
        $this->assertSame('H5P.Foobar', $result->contentType);
        $this->assertSame($library->title, $result->title);
        $this->assertSame('assets/H5P.Foobar-1.2.3/icon.svg', $result->icon);
    }

    public function test_getContentTypeInfo_NoIcon(): void
    {
        /** @var H5PLibrary $library */
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
