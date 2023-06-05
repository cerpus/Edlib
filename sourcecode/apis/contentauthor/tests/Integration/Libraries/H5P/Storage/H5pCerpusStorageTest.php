<?php

namespace Tests\Integration\Libraries\H5P\Storage;

use App\Libraries\ContentAuthorStorage;
use App\Libraries\H5P\Storage\H5PCerpusStorage;
use App\Libraries\H5P\Video\NullVideoAdapter;
use Illuminate\Contracts\Filesystem\Cloud;
use Illuminate\Support\Facades\Storage;
use Psr\Log\NullLogger;
use Tests\TestCase;
use function assert;

class H5pCerpusStorageTest extends TestCase
{
    protected function setUp(): void
    {
        $this->markTestIncomplete('Fix these later');
    }

    public function test_correct_url_without_cdn_prefix()
    {
        $disk = Storage::fake('fake');
        assert($disk instanceof Cloud);

        $disk->put('test.txt', 'some content');

        $cerpusStorage = new H5pCerpusStorage(
            new ContentAuthorStorage($disk),
            new NullLogger(),
            new NullVideoAdapter(),
        );

        $this->assertEquals("/test.txt", $cerpusStorage->getFileUrl('test.txt'));
    }

    public function test_correct_url_with_cdn_prefix()
    {
        $disk = Storage::fake('fake');
        assert($disk instanceof Cloud);

        $disk->put('test.txt', 'some content');

        $cerpusStorage = new H5pCerpusStorage(
            new ContentAuthorStorage($disk),
            new NullLogger(),
            new NullVideoAdapter(),
        );

        $this->assertEquals("http://test/aaa/test.txt", $cerpusStorage->getFileUrl('test.txt'));
    }

    public function test_correct_url_when_file_not_found()
    {
        $disk = Storage::fake('fake');
        assert($disk instanceof Cloud);

        $cerpusStorage = new H5pCerpusStorage(
            new ContentAuthorStorage($disk),
            new NullLogger(),
            new NullVideoAdapter(),
        );


        $this->assertEquals("", $cerpusStorage->getFileUrl('test.txt'));
    }
}
