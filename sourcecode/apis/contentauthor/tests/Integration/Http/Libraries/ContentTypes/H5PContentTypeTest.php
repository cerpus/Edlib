<?php

namespace Tests\Integration\Http\Libraries\ContentTypes;

use App\H5PLibrary;
use App\Http\Libraries\ContentTypes\ContentType;
use App\Http\Libraries\ContentTypes\H5PContentType;
use App\LibraryDescription;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class H5PContentTypeTest extends TestCase
{
    use RefreshDatabase;

    public function test_getContentTypes(): void
    {
        H5PLibrary::factory()->create();
        $controller = new H5PContentType();
        $result = $controller->getContentTypes('abc');
        $this->assertCount(1, $result);

        $item = $result[0];

        $this->assertInstanceOf(ContentType::class, $item);
        $this->assertSame('http://localhost/h5p/create/H5P.Foobar%201.2?redirectToken=abc', $item->createUrl);
        $this->assertSame('H5P.Foobar 1.2', $item->id);
    }
}
