<?php

namespace Tests\H5P\Storage;

use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class H5pCerpusStorage extends TestCase
{
    public function test_correct_url_without_cdn_prefix()
    {
        $disk = Storage::fake('fake');

        $disk->put('test.txt', 'some content');

        $cerpusStorage = new \App\Libraries\H5P\Storage\H5PCerpusStorage($disk, 'fake', $disk, '');

        $this->assertEquals("/test.txt", $cerpusStorage->getFileUrl('test.txt'));
    }

    public function test_correct_url_with_cdn_prefix()
    {
        $disk = Storage::fake('fake');

        $disk->put('test.txt', 'some content');

        $cerpusStorage = new \App\Libraries\H5P\Storage\H5PCerpusStorage($disk, 'fake', $disk, 'http://test/aaa');

        $this->assertEquals("http://test/aaa/test.txt", $cerpusStorage->getFileUrl('test.txt'));
    }

    public function test_correct_url_when_file_not_found()
    {
        $disk = Storage::fake('fake');

        $cerpusStorage = new \App\Libraries\H5P\Storage\H5PCerpusStorage($disk, 'fake', $disk, 'http://test/aaa');

        $this->assertEquals("", $cerpusStorage->getFileUrl('test.txt'));
    }
}
