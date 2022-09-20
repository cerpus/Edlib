<?php

namespace Tests\Integration\Libraries\H5P;

use App\H5PContent;
use App\Libraries\DataObjects\ContentStorageSettings;
use App\Libraries\H5P\H5PExport;
use App\Libraries\H5P\Interfaces\H5PAdapterInterface;
use App\Libraries\H5P\Interfaces\H5PExternalProviderInterface;
use Exception;
use H5PExport as H5PDefaultExport;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Storage;
use Tests\Helpers\MockH5PAdapterInterface;
use Tests\Seeds\TestH5PSeeder;
use Tests\TestCase;
use ZipArchive;

class H5PExportTest extends TestCase
{
    use DatabaseMigrations;
    use RefreshDatabase;
    use WithFaker;
    use MockH5PAdapterInterface;

    private $testDisk;
    private $exportDisk;

    protected function setUp(): void
    {
        parent::setUp();

        $this->testDisk = Storage::disk('testDisk');
        $this->exportDisk = Storage::fake();
        config(['h5p.storage.path' => $this->exportDisk->path("")]);
    }

    private function linkLibrariesFolder()
    {
        symlink($this->testDisk->path('files/libraries'), $this->exportDisk->path('libraries'));
    }

    /**
     * @test
     * @throws Exception
     */
    public function noMultimedia()
    {
        $this->setupH5PAdapter([
            'alterLibrarySemantics' => null,
            'getExternalProviders' => collect(),
        ]);
        $adapter = resolve(H5PAdapterInterface::class);

        $this->linkLibrariesFolder();
        $this->seed(TestH5PSeeder::class);
        $params = '{"text":"<p>Fill in the missing words<\/p>\n","overallFeedback":[{"from":0,"to":100}],"showSolutions":"Show solution","tryAgain":"Retry","checkAnswer":"Check","notFilledOut":"Please fill in all blanks to view solution","answerIsCorrect":"\':ans\' is correct","answerIsWrong":"\':ans\' is wrong","answeredCorrectly":"Answered correctly","answeredIncorrectly":"Answered incorrectly","solutionLabel":"Correct answer:","inputLabel":"Blank input @num of @total","inputHasTipLabel":"Tip available","tipLabel":"Tip","behaviour":{"enableRetry":true,"enableSolutionsButton":true,"enableCheckButton":true,"autoCheck":false,"caseSensitive":true,"showSolutionsRequiresInput":true,"separateLines":false,"disableImageZooming":false,"confirmCheckDialog":false,"confirmRetryDialog":false,"acceptSpellingErrors":false},"scoreBarLabel":"You got :num out of :total points","confirmCheck":{"header":"Finish ?","body":"Are you sure you wish to finish ?","cancelLabel":"Cancel","confirmLabel":"Finish"},"confirmRetry":{"header":"Retry ?","body":"Are you sure you wish to retry ?","cancelLabel":"Cancel","confirmLabel":"Confirm"},"questions":["<p>*Fishing* is a fun *activity*.<\/p>\n"]}';
        $h5p = H5PContent::factory()->create([
            'parameters' => $params,
            'library_id' => 284,
        ]);

        $h5pExport = resolve(H5PDefaultExport::class);
        $export = new H5PExport($h5p, $h5pExport, $adapter);
        $this->assertTrue($export->generateExport(false));
        $exportName = sprintf("%s-%s.", $h5p->slug, $h5p->id);
        $exportPath = sprintf(ContentStorageSettings::EXPORT_PATH, $exportName . "h5p");
        $archivePath = sprintf(ContentStorageSettings::EXPORT_PATH, $exportName . "zip");

        $this->assertFileExists($this->exportDisk->path($exportPath));
        $this->exportDisk->move($exportPath, $archivePath);
        $zipArchive = new ZipArchive();
        $this->assertTrue($zipArchive->open($this->exportDisk->path($archivePath)));
        $this->assertJson($zipArchive->getFromName('content/content.json'));
        $zipArchive->close();
    }

    /**
     * @test
     * @throws Exception
     */
    public function withLocalImage()
    {
        $this->setupH5PAdapter([
            'alterLibrarySemantics' => null,
            'getExternalProviders' => collect(),
        ]);
        $adapter = resolve(H5PAdapterInterface::class);

        $this->linkLibrariesFolder();
        $this->seed(TestH5PSeeder::class);
        $params = '{"media":{"params":{"contentName":"Image","file":{"path":"images\/file-5f6ca98160e6c.jpg","mime":"image\/jpeg","copyright":{"license":"U"},"width":196,"height":358}},"library":"H5P.Image 1.1","metadata":{"contentType":"Image","license":"U","title":"Untitled Image","authors":[],"changes":[],"extraTitle":"Untitled Image"},"subContentId":"ca86c100-d25c-4e19-ac6b-f50f843da292"},"text":"Fill in the missing words","overallFeedback":[{"from":0,"to":100}],"showSolutions":"Show solution","tryAgain":"Retry","checkAnswer":"Check","notFilledOut":"Please fill in all blanks to view solution","answerIsCorrect":"&#039;:ans&#039; is correct","answerIsWrong":"&#039;:ans&#039; is wrong","answeredCorrectly":"Answered correctly","answeredIncorrectly":"Answered incorrectly","solutionLabel":"Correct answer:","inputLabel":"Blank input @num of @total","inputHasTipLabel":"Tip available","tipLabel":"Tip","behaviour":{"enableRetry":true,"enableSolutionsButton":true,"enableCheckButton":true,"autoCheck":false,"caseSensitive":true,"showSolutionsRequiresInput":true,"separateLines":false,"disableImageZooming":false,"confirmCheckDialog":false,"confirmRetryDialog":false,"acceptSpellingErrors":false},"scoreBarLabel":"You got :num out of :total points","confirmCheck":{"header":"Finish ?","body":"Are you sure you wish to finish ?","cancelLabel":"Cancel","confirmLabel":"Finish"},"confirmRetry":{"header":"Retry ?","body":"Are you sure you wish to retry ?","cancelLabel":"Cancel","confirmLabel":"Confirm"},"questions":["<p>Not all *superheros*&nbsp;wear capes!<\/p>\n"]}';
        $h5p = H5PContent::factory()->create([
            'parameters' => $params,
            'library_id' => 284,
        ]);

        $h5pExport = resolve(H5PDefaultExport::class);
        $export = new H5PExport($h5p, $h5pExport, $adapter);
        $this->assertTrue($export->generateExport(false));
        $exportName = sprintf("%s-%s.", $h5p->slug, $h5p->id);
        $exportPath = sprintf(ContentStorageSettings::EXPORT_PATH, $exportName . "h5p");
        $archivePath = sprintf(ContentStorageSettings::EXPORT_PATH, $exportName . "zip");

        $this->assertFileExists($this->exportDisk->path($exportPath));
        $this->exportDisk->move($exportPath, $archivePath);
        $zipArchive = new ZipArchive();
        $this->assertTrue($zipArchive->open($this->exportDisk->path($archivePath)));
        $contentJson = $zipArchive->getFromName('content/content.json');
        $zipArchive->extractTo($this->exportDisk->path(sprintf(ContentStorageSettings::EXPORT_PATH, $exportName)));
        $this->assertJson($contentJson);
        $zipArchive->close();
        $contentDecoded = json_decode($contentJson);
        $this->assertEquals('images/file-5f6ca98160e6c.jpg', $contentDecoded->media->params->file->path);
    }

    /**
     * @test
     * @throws Exception
     */
    public function withRemoteImage_noLocalConvert()
    {
        $this->setupH5PAdapter([
            'alterLibrarySemantics' => null,
            'getExternalProviders' => collect(),
        ]);
        $adapter = resolve(H5PAdapterInterface::class);

        $imageUrl = $this->faker->imageUrl();

        $this->linkLibrariesFolder();
        $this->seed(TestH5PSeeder::class);
        $params = '{"media":{"params":{"contentName":"Image","file":{"path":"' . $imageUrl . '","mime":"image\/jpeg","copyright":{"license":"U"},"width":196,"height":358}},"library":"H5P.Image 1.1","metadata":{"contentType":"Image","license":"U","title":"Untitled Image","authors":[],"changes":[],"extraTitle":"Untitled Image"},"subContentId":"ca86c100-d25c-4e19-ac6b-f50f843da292"},"text":"Fill in the missing words","overallFeedback":[{"from":0,"to":100}],"showSolutions":"Show solution","tryAgain":"Retry","checkAnswer":"Check","notFilledOut":"Please fill in all blanks to view solution","answerIsCorrect":"&#039;:ans&#039; is correct","answerIsWrong":"&#039;:ans&#039; is wrong","answeredCorrectly":"Answered correctly","answeredIncorrectly":"Answered incorrectly","solutionLabel":"Correct answer:","inputLabel":"Blank input @num of @total","inputHasTipLabel":"Tip available","tipLabel":"Tip","behaviour":{"enableRetry":true,"enableSolutionsButton":true,"enableCheckButton":true,"autoCheck":false,"caseSensitive":true,"showSolutionsRequiresInput":true,"separateLines":false,"disableImageZooming":false,"confirmCheckDialog":false,"confirmRetryDialog":false,"acceptSpellingErrors":false},"scoreBarLabel":"You got :num out of :total points","confirmCheck":{"header":"Finish ?","body":"Are you sure you wish to finish ?","cancelLabel":"Cancel","confirmLabel":"Finish"},"confirmRetry":{"header":"Retry ?","body":"Are you sure you wish to retry ?","cancelLabel":"Cancel","confirmLabel":"Confirm"},"questions":["<p>Not all *superheros*&nbsp;wear capes!<\/p>\n"]}';
        $h5p = H5PContent::factory()->create([
            'parameters' => $params,
            'library_id' => 284,
        ]);

        $h5pExport = resolve(H5PDefaultExport::class);
        $export = new H5PExport($h5p, $h5pExport, $adapter);
        $this->assertTrue($export->generateExport(false));
        $exportName = sprintf("%s-%s.", $h5p->slug, $h5p->id);
        $exportPath = sprintf(ContentStorageSettings::EXPORT_PATH, $exportName . "h5p");
        $archivePath = sprintf(ContentStorageSettings::EXPORT_PATH, $exportName . "zip");

        $this->assertFileExists($this->exportDisk->path($exportPath));
        $this->exportDisk->move($exportPath, $archivePath);
        $zipArchive = new ZipArchive();
        $this->assertTrue($zipArchive->open($this->exportDisk->path($archivePath)));
        $contentJson = $zipArchive->getFromName('content/content.json');
        $zipArchive->extractTo($this->exportDisk->path(sprintf(ContentStorageSettings::EXPORT_PATH, $exportName)));
        $this->assertJson($contentJson);
        $zipArchive->close();
        $contentDecoded = json_decode($contentJson);
        $this->assertEquals($imageUrl, $contentDecoded->media->params->file->path);
    }

    /**
     * @test
     * @throws Exception
     */
    public function withRemoteImage_storeLocally()
    {
        $imageUrl = $this->faker->imageUrl();
        $this->linkLibrariesFolder();
        $this->seed(TestH5PSeeder::class);
        $params = '{"media":{"params":{"contentName":"Image","file":{"path":"' . $imageUrl . '","mime":"image\/jpeg","copyright":{"license":"U"},"width":196,"height":358}},"library":"H5P.Image 1.1","metadata":{"contentType":"Image","license":"U","title":"Untitled Image","authors":[],"changes":[],"extraTitle":"Untitled Image"},"subContentId":"ca86c100-d25c-4e19-ac6b-f50f843da292"},"text":"Fill in the missing words","overallFeedback":[{"from":0,"to":100}],"showSolutions":"Show solution","tryAgain":"Retry","checkAnswer":"Check","notFilledOut":"Please fill in all blanks to view solution","answerIsCorrect":"&#039;:ans&#039; is correct","answerIsWrong":"&#039;:ans&#039; is wrong","answeredCorrectly":"Answered correctly","answeredIncorrectly":"Answered incorrectly","solutionLabel":"Correct answer:","inputLabel":"Blank input @num of @total","inputHasTipLabel":"Tip available","tipLabel":"Tip","behaviour":{"enableRetry":true,"enableSolutionsButton":true,"enableCheckButton":true,"autoCheck":false,"caseSensitive":true,"showSolutionsRequiresInput":true,"separateLines":false,"disableImageZooming":false,"confirmCheckDialog":false,"confirmRetryDialog":false,"acceptSpellingErrors":false},"scoreBarLabel":"You got :num out of :total points","confirmCheck":{"header":"Finish ?","body":"Are you sure you wish to finish ?","cancelLabel":"Cancel","confirmLabel":"Finish"},"confirmRetry":{"header":"Retry ?","body":"Are you sure you wish to retry ?","cancelLabel":"Cancel","confirmLabel":"Confirm"},"questions":["<p>Not all *superheros*&nbsp;wear capes!<\/p>\n"]}';
        $h5p = H5PContent::factory()->create([
            'parameters' => $params,
            'library_id' => 284,
        ]);

        $this->exportDisk->makeDirectory('temp');
        $this->setupH5PAdapter([
            'alterLibrarySemantics' => null,
            'getExternalProviders' => function () use ($h5p) {
                $imageProvider1 = $this->getMockBuilder(H5PExternalProviderInterface::class)->getMock();
                $imageProvider1->method('isTargetType')->willReturn(false);
                $imageProvider1->method('getType')->willReturn("image");

                $imageProvider2 = $this->getMockBuilder(H5PExternalProviderInterface::class)->getMock();
                $imageProvider2->method('isTargetType')->willReturn(true);
                $imageProvider2->method('getType')->willReturn("image");
                $imageProvider2->method('storeContent')->willReturnCallback(function () use ($h5p) {
                    $imageDirectory = sprintf(ContentStorageSettings::CONTENT_FULL_PATH, $h5p->id, "image", "tree", 'jpg');
                    $this->exportDisk->makeDirectory(dirname($imageDirectory));
                    $this->exportDisk->put($imageDirectory, $this->testDisk->readStream('files/tree.jpg'));
                    return [
                        'path' => "images/tree.jpg",
                        'mime' => "image/jpeg"
                    ];
                });

                return collect([$imageProvider1, $imageProvider2]);
            },
        ]);
        $adapter = resolve(H5PAdapterInterface::class);

        $h5pExport = resolve(H5PDefaultExport::class);
        $export = new H5PExport($h5p, $h5pExport, $adapter);
        $this->assertTrue($export->generateExport(true));
        $exportName = sprintf("%s-%s.", $h5p->slug, $h5p->id);
        $exportPath = sprintf(ContentStorageSettings::EXPORT_PATH, $exportName . "h5p");
        $archivePath = sprintf(ContentStorageSettings::EXPORT_PATH, $exportName . "zip");

        $this->assertFileExists($this->exportDisk->path($exportPath));
        $this->assertFileExists($this->exportDisk->path($exportPath));
        $this->exportDisk->move($exportPath, $archivePath);
        $zipArchive = new ZipArchive();
        $this->assertTrue($zipArchive->open($this->exportDisk->path($archivePath)));
        $contentJson = $zipArchive->getFromName('content/content.json');
        $zipArchive->extractTo($this->exportDisk->path(sprintf(ContentStorageSettings::EXPORT_PATH, $exportName)));
        $this->assertEquals(83, $zipArchive->locateName("content/images/tree.jpg"));
        $this->assertJson($contentJson);
        $zipArchive->close();
        $contentDecoded = json_decode($contentJson);
        $this->assertEquals("images/tree.jpg", $contentDecoded->media->params->file->path);
    }

    /**
     * @test
     * @throws Exception
     */
    public function withRemoteVideo_storeLocally()
    {
        $this->linkLibrariesFolder();
        $this->seed(TestH5PSeeder::class);
        $params = '{"interactiveVideo":{"video":{"startScreenOptions":{"title":"Interactive Video","hideStartTitle":false,"copyright":""},"textTracks":[{"label":"Subtitles","kind":"subtitles","srcLang":"en"}],"files":[{"path":"https://bc/12456","mime":"video/BrightCove","copyright":{"license":"U"}}]},"assets":{"interactions":[]},"summary":{"task":{"library":"H5P.Summary 1.8","params":{"intro":"Choose the correct statement.","summaries":[{"subContentId":"2363644d-ab92-4f93-8d11-0d6e916bd83d","tip":""}],"overallFeedback":[{"from":0,"to":100}],"solvedLabel":"Progress:","scoreLabel":"Wrong answers:","resultLabel":"Your result","labelCorrect":"Correct.","labelIncorrect":"Incorrect! Please try again.","labelCorrectAnswers":"Correct answers.","tipButtonLabel":"Show tip","scoreBarLabel":"You got :num out of :total points","progressText":"Progress :num of :total"},"subContentId":"211f821f-67f9-4717-a7a4-362c4d507545","metadata":{"contentType":"Summary","license":"U","title":"Untitled Summary"}},"displayAt":3}},"override":{"autoplay":false,"loop":false,"showBookmarksmenuOnLoad":false,"showRewind10":false,"preventSkipping":false,"deactivateSound":false},"l10n":{"interaction":"Interaction","play":"Play","pause":"Pause","mute":"Mute","unmute":"Unmute","quality":"Video Quality","captions":"Captions","close":"Close","fullscreen":"Fullscreen","exitFullscreen":"Exit Fullscreen","summary":"Summary","bookmarks":"Bookmarks","defaultAdaptivitySeekLabel":"Continue","continueWithVideo":"Continue with video","playbackRate":"Playback Rate","rewind10":"Rewind 10 Seconds","navDisabled":"Navigation is disabled","sndDisabled":"Sound is disabled","requiresCompletionWarning":"You need to answer all the questions correctly before continuing.","back":"Back","hours":"Hours","minutes":"Minutes","seconds":"Seconds","currentTime":"Current time:","totalTime":"Total time:","navigationHotkeyInstructions":"Use key k for starting and stopping video at any time","singleInteractionAnnouncement":"Interaction appeared:","multipleInteractionsAnnouncement":"Multiple interactions appeared.","videoPausedAnnouncement":"Video is paused","content":"Content"}}';
        $h5p = H5PContent::factory()->create([
            'parameters' => $params,
            'library_id' => 202,
        ]);

        $this->exportDisk->makeDirectory('temp');
        $this->setupH5PAdapter([
            'alterLibrarySemantics' => null,
            'getExternalProviders' => function () use ($h5p) {
                $imageProvider = $this->getMockBuilder(H5PExternalProviderInterface::class)->getMock();
                $imageProvider->method('isTargetType')->willReturn(false);
                $imageProvider->method('getType')->willReturn("image");

                $videoProvider = $this->getMockBuilder(H5PExternalProviderInterface::class)->getMock();
                $videoProvider->method('isTargetType')->willReturn(true);
                $videoProvider->method('getType')->willReturn("video");
                $videoProvider->method('storeContent')->willReturnCallback(function () use ($h5p) {
                    $this->exportDisk->put("content/{$h5p->id}/videos/sampleVideo.mp4", $this->testDisk->readStream("files/sample.mp4"));
                    return [
                        'path' => "videos/sampleVideo.mp4",
                        'mime' => "video/mp4"
                    ];
                });

                return collect([$imageProvider, $videoProvider]);
            },
        ]);
        $adapter = resolve(H5PAdapterInterface::class);

        $h5pExport = resolve(H5PDefaultExport::class);
        $export = new H5PExport($h5p, $h5pExport, $adapter);
        $this->assertTrue($export->generateExport(true));
        $exportName = sprintf("%s-%s.", $h5p->slug, $h5p->id);
        $exportPath = sprintf(ContentStorageSettings::EXPORT_PATH, $exportName . "h5p");
        $archivePath = sprintf(ContentStorageSettings::EXPORT_PATH, $exportName . "zip");

        $this->assertFileExists($this->exportDisk->path($exportPath));
        $this->exportDisk->move($exportPath, $archivePath);
        $zipArchive = new ZipArchive();
        $this->assertTrue($zipArchive->open($this->exportDisk->path($archivePath)));
        $contentJson = $zipArchive->getFromName('content/content.json');
        $zipArchive->extractTo($this->exportDisk->path(sprintf(ContentStorageSettings::EXPORT_PATH, $exportName)));
        $this->assertEquals(100, $zipArchive->locateName("content/videos/sampleVideo.mp4"));
        $this->assertJson($contentJson);
        $zipArchive->close();
        $contentDecoded = json_decode($contentJson);
        $this->assertEquals("videos/sampleVideo.mp4", $contentDecoded->interactiveVideo->video->files[0]->path);
    }


    /**
     * @test
     * @throws Exception
     */
    public function withRemoteVideoAndImage_storeLocally()
    {
        $imageUrl = $this->faker->imageUrl();
        $this->linkLibrariesFolder();
        $this->seed(TestH5PSeeder::class);
        $params = '{"interactiveVideo":{"video":{"startScreenOptions":{"title":"Interactive Video","hideStartTitle":false,"copyright":""},"textTracks":[{"label":"Subtitles","kind":"subtitles","srcLang":"en"}],"files":[{"path":"https://bc/ref:12456","mime":"video/BrightCove","copyright":{"license":"U"}}]},"assets":{"interactions":[{"x":5.230125523012552,"y":13.011152416356877,"width":14.986376021798364,"height":10,"duration":{"from":0,"to":10},"libraryTitle":"Image","action":{"library":"H5P.Image 1.0","params":{"contentName":"Image","file":{"path":"' . $imageUrl . '","mime":"image/jpeg","copyright":{"license":"U"},"width":1100,"height":734},"alt":"test"},"subContentId":"b0bd1110-ada3-4afe-9be9-1a822ea9643d","metadata":{"contentType":"Image","license":"U"}},"visuals":{"backgroundColor":"rgba(0,0,0,0)","boxShadow":true},"pause":false,"displayType":"poster","buttonOnMobile":false,"goto":{"url":{"protocol":"http://"},"visualize":false,"type":""},"label":""}]},"summary":{"task":{"library":"H5P.Summary 1.8","params":{"intro":"Choose the correct statement.","summaries":[{"subContentId":"2363644d-ab92-4f93-8d11-0d6e916bd83d","tip":""}],"overallFeedback":[{"from":0,"to":100}],"solvedLabel":"Progress:","scoreLabel":"Wrong answers:","resultLabel":"Your result","labelCorrect":"Correct.","labelIncorrect":"Incorrect! Please try again.","labelCorrectAnswers":"Correct answers.","tipButtonLabel":"Show tip","scoreBarLabel":"You got :num out of :total points","progressText":"Progress :num of :total"},"subContentId":"211f821f-67f9-4717-a7a4-362c4d507545","metadata":{"contentType":"Summary","license":"U","title":"Untitled Summary"}},"displayAt":3}},"override":{"autoplay":false,"loop":false,"showBookmarksmenuOnLoad":false,"showRewind10":false,"preventSkipping":false,"deactivateSound":false},"l10n":{"interaction":"Interaction","play":"Play","pause":"Pause","mute":"Mute","unmute":"Unmute","quality":"Video Quality","captions":"Captions","close":"Close","fullscreen":"Fullscreen","exitFullscreen":"Exit Fullscreen","summary":"Summary","bookmarks":"Bookmarks","defaultAdaptivitySeekLabel":"Continue","continueWithVideo":"Continue with video","playbackRate":"Playback Rate","rewind10":"Rewind 10 Seconds","navDisabled":"Navigation is disabled","sndDisabled":"Sound is disabled","requiresCompletionWarning":"You need to answer all the questions correctly before continuing.","back":"Back","hours":"Hours","minutes":"Minutes","seconds":"Seconds","currentTime":"Current time:","totalTime":"Total time:","navigationHotkeyInstructions":"Use key k for starting and stopping video at any time","singleInteractionAnnouncement":"Interaction appeared:","multipleInteractionsAnnouncement":"Multiple interactions appeared.","videoPausedAnnouncement":"Video is paused","content":"Content"}}';
        $h5p = H5PContent::factory()->create([
            'parameters' => $params,
            'library_id' => 202,
        ]);

        $this->exportDisk->makeDirectory('temp');
        $this->setupH5PAdapter([
            'alterLibrarySemantics' => null,
            'getExternalProviders' => function () use ($h5p, $imageUrl) {
                $imageProvider = $this->getMockBuilder(H5PExternalProviderInterface::class)->getMock();
                $imageProvider
                    ->expects($this->exactly(2))
                    ->method('isTargetType')
                    ->withConsecutive(
                        [$this->equalTo('video/BrightCove'), $this->equalTo('https://bc/ref:12456')],
                        [$this->equalTo('image/jpeg'), $this->equalTo($imageUrl)]
                    )
                    ->willReturnOnConsecutiveCalls(false, true);
                $imageProvider->method('getType')->willReturn("image");
                $imageProvider->method('storeContent')->willReturnCallback(function () use ($h5p) {
                    $imageDirectory = sprintf(ContentStorageSettings::CONTENT_FULL_PATH, $h5p->id, "image", "tree", 'jpg');
                    $this->exportDisk->makeDirectory(dirname($imageDirectory));
                    $this->exportDisk->put($imageDirectory, $this->testDisk->readStream('files/tree.jpg'));
                    return [
                        'path' => "images/tree.jpg",
                        'mime' => "image/jpeg"
                    ];
                });

                $videoProvider = $this->getMockBuilder(H5PExternalProviderInterface::class)->getMock();
                $videoProvider->method('isTargetType')->willReturn(true);
                $videoProvider->method('getType')->willReturn("video");
                $videoProvider->method('storeContent')->willReturnCallback(function () use ($h5p) {
                    $this->exportDisk->put("content/{$h5p->id}/videos/sampleVideo.mp4", $this->testDisk->readStream("files/sample.mp4"));
                    return [
                        'path' => "videos/sampleVideo.mp4",
                        'mime' => "video/mp4"
                    ];
                });

                return collect([$imageProvider, $videoProvider]);
            },
        ]);
        $adapter = resolve(H5PAdapterInterface::class);

        $h5pExport = resolve(H5PDefaultExport::class);
        $export = new H5PExport($h5p, $h5pExport, $adapter);
        $this->assertTrue($export->generateExport(true));
        $exportName = sprintf("%s-%s.", $h5p->slug, $h5p->id);
        $exportPath = sprintf(ContentStorageSettings::EXPORT_PATH, $exportName . "h5p");
        $archivePath = sprintf(ContentStorageSettings::EXPORT_PATH, $exportName . "zip");

        $this->assertFileExists($this->exportDisk->path($exportPath));
        $this->exportDisk->move($exportPath, $archivePath);
        $zipArchive = new ZipArchive();
        $this->assertTrue($zipArchive->open($this->exportDisk->path($archivePath)));
        $contentJson = $zipArchive->getFromName('content/content.json');
        $zipArchive->extractTo($this->exportDisk->path(sprintf(ContentStorageSettings::EXPORT_PATH, $exportName)));
        $this->assertEquals(101, $zipArchive->locateName("content/videos/sampleVideo.mp4"));
        $this->assertEquals(100, $zipArchive->locateName("content/images/tree.jpg"));
        $this->assertJson($contentJson);
        $zipArchive->close();
        $contentDecoded = json_decode($contentJson);
        $this->assertEquals("videos/sampleVideo.mp4", $contentDecoded->interactiveVideo->video->files[0]->path);
        $this->assertEquals("images/tree.jpg", $contentDecoded->interactiveVideo->assets->interactions[0]->action->params->file->path);
    }

    /**
     * @test
     * @throws Exception
     */
    public function withRemoteCaptions_storeLocally()
    {
        $this->linkLibrariesFolder();
        $this->seed(TestH5PSeeder::class);
        $params = '{"interactiveVideo":{"video":{"startScreenOptions":{"title":"Interactive Video","hideStartTitle":false,"copyright":""},"textTracks":[{"kind":"captions","label":"Nynorsk","srcLang":"nn","track":{"externalId":"87ada56a-5670-4b43-96e3-4aef54284197","path":"https:\/\/urltocaptions/text.vtt","mime":"text\/webvtt","copyright":{"license":"U"}}}],"files":[{"path":"https://bc/12456","mime":"video/BrightCove","copyright":{"license":"U"}}]},"assets":{"interactions":[]},"summary":{"task":{"library":"H5P.Summary 1.8","params":{"intro":"Choose the correct statement.","summaries":[{"subContentId":"2363644d-ab92-4f93-8d11-0d6e916bd83d","tip":""}],"overallFeedback":[{"from":0,"to":100}],"solvedLabel":"Progress:","scoreLabel":"Wrong answers:","resultLabel":"Your result","labelCorrect":"Correct.","labelIncorrect":"Incorrect! Please try again.","labelCorrectAnswers":"Correct answers.","tipButtonLabel":"Show tip","scoreBarLabel":"You got :num out of :total points","progressText":"Progress :num of :total"},"subContentId":"211f821f-67f9-4717-a7a4-362c4d507545","metadata":{"contentType":"Summary","license":"U","title":"Untitled Summary"}},"displayAt":3}},"override":{"autoplay":false,"loop":false,"showBookmarksmenuOnLoad":false,"showRewind10":false,"preventSkipping":false,"deactivateSound":false},"l10n":{"interaction":"Interaction","play":"Play","pause":"Pause","mute":"Mute","unmute":"Unmute","quality":"Video Quality","captions":"Captions","close":"Close","fullscreen":"Fullscreen","exitFullscreen":"Exit Fullscreen","summary":"Summary","bookmarks":"Bookmarks","defaultAdaptivitySeekLabel":"Continue","continueWithVideo":"Continue with video","playbackRate":"Playback Rate","rewind10":"Rewind 10 Seconds","navDisabled":"Navigation is disabled","sndDisabled":"Sound is disabled","requiresCompletionWarning":"You need to answer all the questions correctly before continuing.","back":"Back","hours":"Hours","minutes":"Minutes","seconds":"Seconds","currentTime":"Current time:","totalTime":"Total time:","navigationHotkeyInstructions":"Use key k for starting and stopping video at any time","singleInteractionAnnouncement":"Interaction appeared:","multipleInteractionsAnnouncement":"Multiple interactions appeared.","videoPausedAnnouncement":"Video is paused","content":"Content"}}';
        $h5p = H5PContent::factory()->create([
            'parameters' => $params,
            'library_id' => 202,
        ]);

        $this->exportDisk->makeDirectory('temp');
        $this->setupH5PAdapter([
            'alterLibrarySemantics' => null,
            'getExternalProviders' => function () use ($h5p) {
                $textTrackProvider = $this->getMockBuilder(H5PExternalProviderInterface::class)->getMock();
                $textTrackProvider
                    ->expects($this->exactly(2))
                    ->method('isTargetType')
                    ->withConsecutive(
                        [$this->equalTo('text/webvtt'), $this->equalTo('https://urltocaptions/text.vtt')],
                        [$this->equalTo('video/BrightCove'), $this->equalTo('https://bc/12456')]
                    )
                    ->willReturnOnConsecutiveCalls(true, false);

                $textTrackProvider->method('isTargetType')->willReturn(true);
                $textTrackProvider->method('getType')->willReturn("file");
                $textTrackProvider->method('storeContent')->willReturnCallback(function () use ($h5p) {
                    $this->exportDisk->put("content/{$h5p->id}/files/videoCaption.vtt", 'This is a caption file, though not correctly formatted');
                    return [
                        'path' => "files/videoCaption.vtt",
                        'mime' => "text/webvtt"
                    ];
                });

                $videoProvider = $this->getMockBuilder(H5PExternalProviderInterface::class)->getMock();
                $videoProvider->method('isTargetType')->willReturn(true);
                $videoProvider->method('getType')->willReturn("video");
                $videoProvider->method('storeContent')->willReturnCallback(function () use ($h5p) {
                    $this->exportDisk->put("content/{$h5p->id}/videos/sampleVideo.mp4", $this->testDisk->readStream("files/sample.mp4"));
                    return [
                        'path' => "videos/sampleVideo.mp4",
                        'mime' => "video/mp4"
                    ];
                });

                return collect([$textTrackProvider, $videoProvider]);
            },
        ]);
        $adapter = resolve(H5PAdapterInterface::class);

        $h5pExport = resolve(H5PDefaultExport::class);
        $export = new H5PExport($h5p, $h5pExport, $adapter);
        $this->assertTrue($export->generateExport(true));
        $exportName = sprintf("%s-%s.", $h5p->slug, $h5p->id);
        $exportPath = sprintf(ContentStorageSettings::EXPORT_PATH, $exportName . "h5p");
        $archivePath = sprintf(ContentStorageSettings::EXPORT_PATH, $exportName . "zip");

        $this->assertFileExists($this->exportDisk->path($exportPath));
        $this->exportDisk->move($exportPath, $archivePath);
        $zipArchive = new ZipArchive();
        $this->assertTrue($zipArchive->open($this->exportDisk->path($archivePath)));
        $contentJson = $zipArchive->getFromName('content/content.json');
        $zipArchive->extractTo($this->exportDisk->path(sprintf(ContentStorageSettings::EXPORT_PATH, $exportName)));
        $this->assertEquals(100, $zipArchive->locateName("content/files/videoCaption.vtt"));
        $this->assertEquals(101, $zipArchive->locateName("content/videos/sampleVideo.mp4"));
        $this->assertJson($contentJson);
        $zipArchive->close();
        $contentDecoded = json_decode($contentJson);
        $this->assertEquals("videos/sampleVideo.mp4", $contentDecoded->interactiveVideo->video->files[0]->path);
        $this->assertEquals("files/videoCaption.vtt", $contentDecoded->interactiveVideo->video->textTracks[0]->track->path);
    }
}
