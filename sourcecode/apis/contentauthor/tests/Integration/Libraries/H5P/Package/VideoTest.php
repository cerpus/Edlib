<?php

namespace Tests\Integration\Libraries\H5P\Package;

use App\Libraries\H5P\Packages\Video;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class VideoTest extends TestCase
{
    use WithFaker;

    /**
     * @test
     */
    public function alterSource_thenSuccess()
    {
        $orginalFile = 'videos/sources-originalFile';
        $newFileUrl = $this->faker->url;
        $mimeType = $this->faker->mimeType();
        $packageSemantics = '{"visuals":{"fit":true,"controls":true},"playback":{"autoplay":false,"loop":false},"l10n":{"name":"Video","loading":"Video player loading...","noPlayers":"Found no video players that supports the given video format.","noSources":"Video is missing sources.","aborted":"Media playback has been aborted.","networkFailure":"Network failure.","cannotDecode":"Unable to decode media.","formatNotSupported":"Video format not supported.","mediaEncrypted":"Media encrypted.","unknownError":"Unknown error.","invalidYtId":"Invalid YouTube ID.","unknownYtId":"Unable to find video with the given YouTube ID.","restrictedYt":"The owner of this video does not allow it to be embedded."},"sources":[{"path":"' . $orginalFile . '#tmp","mime":"video/mp4","copyright":{"license":"U"}}]}';
        $video = new Video($packageSemantics);
        $newSource = [
            $newFileUrl,
            $mimeType,
        ];
        $this->assertTrue($video->alterSource($orginalFile, $newSource));

        $packageSemanticsObject = json_decode($packageSemantics);
        $packageSemanticsObject->sources[0]->path = $newFileUrl;
        $packageSemanticsObject->sources[0]->mime = $mimeType;
        $this->assertEquals($video->getPackageStructure(), $packageSemanticsObject);
    }
}
