<?php

namespace Tests\Integration\Libraries\H5P\Package;

use App\Libraries\H5P\Packages\CoursePresentation;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class CoursePresentationTest extends TestCase
{
    use WithFaker;

    private array $structure = [
        'twoSlidesWithThreeElements' => '{"presentation":{"slides":[{"elements":[{"x":1.0893246187363834,"y":2.150537634408602,"width":78.21350762527233,"height":70.53763440860214,"action":{"library":"H5P.Video 1.3","params":{"visuals":{"fit":true,"controls":true},"playback":{"autoplay":false,"loop":false},"l10n":{"name":"Video","loading":"Video player loading...","noPlayers":"Found no video players that supports the given video format.","noSources":"Video is missing sources.","aborted":"Media playback has been aborted.","networkFailure":"Network failure.","cannotDecode":"Unable to decode media.","formatNotSupported":"Video format not supported.","mediaEncrypted":"Media encrypted.","unknownError":"Unknown error.","invalidYtId":"Invalid YouTube ID.","unknownYtId":"Unable to find video with the given YouTube ID.","restrictedYt":"The owner of this video does not allow it to be embedded."},"sources":[{"path":"videos/sources-5a37b375db9b1.mp4#tmp","mime":"video/mp4","copyright":{"license":"U"}}]},"subContentId":"251ec718-6347-4d1f-9f61-44d4589f5fc6"},"alwaysDisplayComments":false,"backgroundOpacity":0,"displayAsButton":false,"invisible":false,"solution":""},{"x":59.694989106753816,"y":59.56989247311827,"width":40,"height":40,"action":{"library":"H5P.SingleChoiceSet 1.8","params":{"choices":[{"subContentId":"5263a21d-d775-46bf-b1a3-b89a45bd8dae","question":"<p>test</p>\n","answers":["<p>c</p>\n","<p>w</p>\n"]},{"subContentId":"238a1e73-76a4-463f-b4a3-0382fa733a6f"}],"behaviour":{"timeoutCorrect":2000,"timeoutWrong":3000,"soundEffectsEnabled":true,"enableRetry":true,"enableSolutionsButton":true,"passPercentage":100},"l10n":{"resultSlideTitle":"You got :numcorrect of :maxscore correct","showSolutionButtonLabel":"Show solution","retryButtonLabel":"Retry","solutionViewTitle":"Solution","correctText":"Correct!","incorrectText":"Incorrect!","muteButtonLabel":"Mute feedback sound","closeButtonLabel":"Close","slideOfTotal":"Slide :num of :total"}},"subContentId":"4c4f8167-ad4d-4045-9808-f4d9ac4c236c"},"alwaysDisplayComments":false,"backgroundOpacity":0,"displayAsButton":false,"invisible":false,"solution":""}],"keywords":[],"slideBackgroundSelector":{}},{"elements":[{"x":27.233115468409586,"y":32.25806451612903,"width":13.470125450645853,"height":40,"action":{"library":"H5P.Image 1.0","params":{"contentName":"Image","file":{"path":"images/file-5a37b81aa4f7a.jpg#tmp","mime":"image/jpeg","copyright":{"license":"U"},"width":600,"height":902}},"subContentId":"5200ba68-8fdc-499d-9e71-70cccbe8c25e"},"alwaysDisplayComments":false,"backgroundOpacity":0,"displayAsButton":false,"invisible":false,"solution":""}],"keywords":[],"slideBackgroundSelector":{}}]}}',
        'threeSlidesWithFiveElements' => '{"presentation":{"slides":[{"elements":[{"x":1.0893246187363834,"y":2.150537634408602,"width":78.21350762527233,"height":70.53763440860214,"action":{"library":"H5P.Video 1.3","params":{"visuals":{"fit":true,"controls":true},"playback":{"autoplay":false,"loop":false},"l10n":{"name":"Video","loading":"Video player loading...","noPlayers":"Found no video players that supports the given video format.","noSources":"Video is missing sources.","aborted":"Media playback has been aborted.","networkFailure":"Network failure.","cannotDecode":"Unable to decode media.","formatNotSupported":"Video format not supported.","mediaEncrypted":"Media encrypted.","unknownError":"Unknown error.","invalidYtId":"Invalid YouTube ID.","unknownYtId":"Unable to find video with the given YouTube ID.","restrictedYt":"The owner of this video does not allow it to be embedded."},"sources":[{"path":"videos/sources-5b37b375db9b1.mp4#tmp","mime":"video/mp4","copyright":{"license":"U"}}]},"subContentId":"251ec718-6347-4d1f-9f61-44d4589f5fc6"},"alwaysDisplayComments":false,"backgroundOpacity":0,"displayAsButton":false,"invisible":false,"solution":""},{"x":59.694989106753816,"y":59.56989247311827,"width":40,"height":40,"action":{"library":"H5P.SingleChoiceSet 1.8","params":{"choices":[{"subContentId":"5263a21d-d775-46bf-b1a3-b89a45bd8dae","question":"<p>test</p>\n","answers":["<p>c</p>\n","<p>w</p>\n"]},{"subContentId":"238a1e73-76a4-463f-b4a3-0382fa733a6f"}],"behaviour":{"timeoutCorrect":2000,"timeoutWrong":3000,"soundEffectsEnabled":true,"enableRetry":true,"enableSolutionsButton":true,"passPercentage":100},"l10n":{"resultSlideTitle":"You got :numcorrect of :maxscore correct","showSolutionButtonLabel":"Show solution","retryButtonLabel":"Retry","solutionViewTitle":"Solution","correctText":"Correct!","incorrectText":"Incorrect!","muteButtonLabel":"Mute feedback sound","closeButtonLabel":"Close","slideOfTotal":"Slide :num of :total"}},"subContentId":"4c4f8167-ad4d-4045-9808-f4d9ac4c236c"},"alwaysDisplayComments":false,"backgroundOpacity":0,"displayAsButton":false,"invisible":false,"solution":""}],"keywords":[],"slideBackgroundSelector":{}},{"elements":[{"x":27.233115468409586,"y":32.25806451612903,"width":13.470125450645853,"height":40,"action":{"library":"H5P.Image 1.0","params":{"contentName":"Image","file":{"path":"images/file-5a37b81aa4f7a.jpg#tmp","mime":"image/jpeg","copyright":{"license":"U"},"width":600,"height":902}},"subContentId":"5200ba68-8fdc-499d-9e71-70cccbe8c25e"},"alwaysDisplayComments":false,"backgroundOpacity":0,"displayAsButton":false,"invisible":false,"solution":""},{"x":1.0893246187363834,"y":2.150537634408602,"width":78.21350762527233,"height":70.53763440860214,"action":{"library":"H5P.Video 1.3","params":{"visuals":{"fit":true,"controls":true},"playback":{"autoplay":false,"loop":false},"l10n":{"name":"Video","loading":"Video player loading...","noPlayers":"Found no video players that supports the given video format.","noSources":"Video is missing sources.","aborted":"Media playback has been aborted.","networkFailure":"Network failure.","cannotDecode":"Unable to decode media.","formatNotSupported":"Video format not supported.","mediaEncrypted":"Media encrypted.","unknownError":"Unknown error.","invalidYtId":"Invalid YouTube ID.","unknownYtId":"Unable to find video with the given YouTube ID.","restrictedYt":"The owner of this video does not allow it to be embedded."},"sources":[{"path":"videos/sources-5a37b375db9b1.mp4#tmp","mime":"video/mp4","copyright":{"license":"U"}}]},"subContentId":"251ec718-6347-4d1f-9f61-44d4589f5fc6"},"alwaysDisplayComments":false,"backgroundOpacity":0,"displayAsButton":false,"invisible":false,"solution":""}],"keywords":[],"slideBackgroundSelector":{}},{"elements":[{"x":27.233115468409586,"y":32.25806451612903,"width":13.470125450645853,"height":40,"action":{"library":"H5P.Image 1.0","params":{"contentName":"Image","file":{"path":"images/file-5a37b81aa4f7a.jpg#tmp","mime":"image/jpeg","copyright":{"license":"U"},"width":600,"height":902}},"subContentId":"5200ba68-8fdc-499d-9e71-70cccbe8c25e"},"alwaysDisplayComments":false,"backgroundOpacity":0,"displayAsButton":false,"invisible":false,"solution":""}],"keywords":[],"slideBackgroundSelector":{}}]}}',
        'oneSlidesWithEmptyAction' => '{"presentation":{"slides":[{"elements":[{"x":1.0893246187363834,"y":2.150537634408602,"width":78.21350762527233,"height":70.53763440860214,"action":"not an object","alwaysDisplayComments":false,"backgroundOpacity":0,"displayAsButton":false,"invisible":false,"solution":""}],"keywords":[],"slideBackgroundSelector":{}}]}}',
    ];

    /**
     * @test
     */
    public function alterSource()
    {
        $coursePresentationSemantics = $this->structure['twoSlidesWithThreeElements'];
        $coursePresentation = new CoursePresentation($coursePresentationSemantics);
        $sourceFile = 'videos/sources-5a37b375db9b1.mp4';
        $newFileUrl = $this->faker->url;
        $mimeType = $this->faker->mimeType();

        $newSource = [
            $newFileUrl,
            $mimeType,
        ];

        $this->assertTrue($coursePresentation->alterSource($sourceFile, $newSource));

        $coursePresentationSemanticsObject = json_decode($coursePresentationSemantics);
        $coursePresentationSemanticsObject->presentation->slides[0]->elements[0]->action->params->sources[0]->path = $newFileUrl;
        $coursePresentationSemanticsObject->presentation->slides[0]->elements[0]->action->params->sources[0]->mime = $mimeType;
        $this->assertEquals($coursePresentation->getPackageStructure(), $coursePresentationSemanticsObject);

        $coursePresentationSemantics = $this->structure['threeSlidesWithFiveElements'];
        $coursePresentation = new CoursePresentation($coursePresentationSemantics);
        $newFileUrl = $this->faker->url;
        $mimeType = $this->faker->mimeType();

        $newSource = [
            $newFileUrl,
            $mimeType,
        ];

        $this->assertTrue($coursePresentation->alterSource($sourceFile, $newSource));

        $coursePresentationSemanticsObject = json_decode($coursePresentationSemantics);
        $coursePresentationSemanticsObject->presentation->slides[1]->elements[1]->action->params->sources[0]->path = $newFileUrl;
        $coursePresentationSemanticsObject->presentation->slides[1]->elements[1]->action->params->sources[0]->mime = $mimeType;
        $this->assertEquals($coursePresentation->getPackageStructure(), $coursePresentationSemanticsObject);
    }

    /**
     * @test
     */
    public function emptyAction()
    {
        $coursePresentationSemantics = $this->structure['oneSlidesWithEmptyAction'];
        $coursePresentation = new CoursePresentation($coursePresentationSemantics);
        $sourceFile = 'videos/sources-5a37b375db9b1.mp4';
        $newFileUrl = $this->faker->url;
        $mimeType = $this->faker->mimeType();

        $newSource = [
            $newFileUrl,
            $mimeType,
        ];

        $this->assertTrue($coursePresentation->alterSource($sourceFile, $newSource));
    }
}
