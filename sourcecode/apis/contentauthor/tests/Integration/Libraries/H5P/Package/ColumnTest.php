<?php

namespace Tests\Integration\Libraries\H5P\Package;

use App\Libraries\H5P\Packages\Column;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ColumnTest extends TestCase
{
    use WithFaker;

    private array $structures = [
        'columnWithOneVideoOneTextAndOneCollage' => '{"content":[{"content":{"params":{"visuals":{"fit":true,"controls":true},"playback":{"autoplay":false,"loop":false},"l10n":{"name":"Video","loading":"Video player loading...","noPlayers":"Found no video players that supports the given video format.","noSources":"Video is missing sources.","aborted":"Media playback has been aborted.","networkFailure":"Network failure.","cannotDecode":"Unable to decode media.","formatNotSupported":"Video format not supported.","mediaEncrypted":"Media encrypted.","unknownError":"Unknown error.","invalidYtId":"Invalid YouTube ID.","unknownYtId":"Unable to find video with the given YouTube ID.","restrictedYt":"The owner of this video does not allow it to be embedded."},"sources":[{"path":"videos/myVideo.mp4#tmp","mime":"video/mp4","copyright":{"license":"U"}}]},"library":"H5P.Video 1.3","subContentId":"6472809a-8eff-4987-ab9f-1079d9fe8a95"},"useSeparator":"auto"},{"content":{"params":{"text":"<p>Se opp!</p>\n"},"library":"H5P.AdvancedText 1.1","subContentId":"a5cd804e-ca65-419d-8c16-a7415821c4fa"},"useSeparator":"auto"},{"content":{"params":{"collage":{"template":"1","options":{"heightRatio":0.75,"spacing":0.5,"frame":true},"clips":[{"image":{"width":3000,"height":3000,"mime":"image/jpeg","path":"images/collageClip-5a3a5071b44dc.jpg#tmp"},"scale":1,"offset":{"top":0,"left":0}}]}},"library":"H5P.Collage 0.3","subContentId":"15c91b99-5cfe-4ce4-b7a0-519844ef9613"},"useSeparator":"auto"}]}',
    ];

    /**
     * @test
     */
    public function alterSource_thenSuccess()
    {
        $columnSemantics = $this->structures['columnWithOneVideoOneTextAndOneCollage'];
        $column = new Column($columnSemantics);

        $source = 'videos/myVideo.mp4';
        $newFileUrl = $this->faker->url;
        $mimeType = $this->faker->mimeType();
        $newSource = [
            $newFileUrl,
            $mimeType,
        ];

        $this->assertTrue($column->alterSource($source, $newSource));

        $columnSemanticsObject = json_decode($columnSemantics);
        $columnSemanticsObject->content[0]->content->params->sources[0]->path = $newFileUrl;
        $columnSemanticsObject->content[0]->content->params->sources[0]->mime = $mimeType;
        $this->assertEquals($column->getPackageStructure(), $columnSemanticsObject);
    }
}
