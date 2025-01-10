<?php

namespace Tests\Integration\Jobs;

use App\ContentVersion;
use App\H5PContent;
use App\H5PContentsVideo;
use App\Jobs\PingVideoApi;
use App\Libraries\H5P\Interfaces\H5PVideoInterface;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Testing\Concerns\InteractsWithDatabase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use Tests\Helpers\VersionedH5PTrait;
use Tests\Seeds\TestH5PSeeder;
use Tests\TestCase;

class PingVideoApiTest extends TestCase
{
    use RefreshDatabase;
    use VersionedH5PTrait;
    use InteractsWithDatabase;
    use WithFaker;

    private array $packageStructure = [
        'interactiveVideoWithLocalVideoSource' => '{"interactiveVideo":{"video":{"startScreenOptions":{"title":"Interactive Video","hideStartTitle":false,"copyright":""},"textTracks":[{"label":"Subtitles","kind":"subtitles","srcLang":"en"}],"files":[{"path":"videos\/files-5a337db5cdf93.mp4#tmp","mime":"video\/mp4","copyright":{"license":"U"}}]},"assets":{"interactions":[],"bookmarks":[]},"summary":{"task":{"library":"H5P.Summary 1.8","params":{"intro":"Choose the correct statement.","summaries":[{"subContentId":"1dd50eaf-d839-43eb-b41a-091a7f39874d","tip":""}],"overallFeedback":[{"from":0,"to":100}],"solvedLabel":"Progress:","scoreLabel":"Wrong answers:","resultLabel":"Your result","labelCorrect":"Correct.","labelIncorrect":"Incorrect! Please try again.","labelCorrectAnswers":"Correct answers."},"subContentId":"37b2670f-e199-4f76-bd33-0d6ea81efcce"},"displayAt":3}},"override":{"autoplay":false,"loop":false,"showBookmarksmenuOnLoad":false,"showRewind10":false,"preventSkipping":false,"deactivateSound":false},"l10n":{"interaction":"Interaction","play":"Play","pause":"Pause","mute":"Mute","unmute":"Unmute","quality":"Video Quality","captions":"Captions","close":"Close","fullscreen":"Fullscreen","exitFullscreen":"Exit Fullscreen","summary":"Summary","bookmarks":"Bookmarks","defaultAdaptivitySeekLabel":"Continue","continueWithVideo":"Continue with video","playbackRate":"Playback Rate","rewind10":"Rewind 10 Seconds","navDisabled":"Navigation is disabled","sndDisabled":"Sound is disabled","requiresCompletionWarning":"You need to answer all the questions correctly before continuing.","back":"Back","hours":"Hours","minutes":"Minutes","seconds":"Seconds","currentTime":"Current time:","totalTime":"Total time:","navigationHotkeyInstructions":"Use key k for starting and stopping video at any time","singleInteractionAnnouncement":"Interaction appeared:","multipleInteractionsAnnouncement":"Multiple interactions appeared.","videoPausedAnnouncement":"Video is paused"}}',
        'interactiveVideoWithNoLocalVideoFiles' => '{"interactiveVideo":{"video":{"startScreenOptions":{"title":"Interactive Video","hideStartTitle":false,"copyright":""},"textTracks":[{"label":"Subtitles","kind":"subtitles","srcLang":"en"}],"files":[]},"assets":{"interactions":[],"bookmarks":[]},"summary":{"task":{"library":"H5P.Summary 1.8","params":{"intro":"Choose the correct statement.","summaries":[{"subContentId":"1dd50eaf-d839-43eb-b41a-091a7f39874d","tip":""}],"overallFeedback":[{"from":0,"to":100}],"solvedLabel":"Progress:","scoreLabel":"Wrong answers:","resultLabel":"Your result","labelCorrect":"Correct.","labelIncorrect":"Incorrect! Please try again.","labelCorrectAnswers":"Correct answers."},"subContentId":"37b2670f-e199-4f76-bd33-0d6ea81efcce"},"displayAt":3}},"override":{"autoplay":false,"loop":false,"showBookmarksmenuOnLoad":false,"showRewind10":false,"preventSkipping":false,"deactivateSound":false},"l10n":{"interaction":"Interaction","play":"Play","pause":"Pause","mute":"Mute","unmute":"Unmute","quality":"Video Quality","captions":"Captions","close":"Close","fullscreen":"Fullscreen","exitFullscreen":"Exit Fullscreen","summary":"Summary","bookmarks":"Bookmarks","defaultAdaptivitySeekLabel":"Continue","continueWithVideo":"Continue with video","playbackRate":"Playback Rate","rewind10":"Rewind 10 Seconds","navDisabled":"Navigation is disabled","sndDisabled":"Sound is disabled","requiresCompletionWarning":"You need to answer all the questions correctly before continuing.","back":"Back","hours":"Hours","minutes":"Minutes","seconds":"Seconds","currentTime":"Current time:","totalTime":"Total time:","navigationHotkeyInstructions":"Use key k for starting and stopping video at any time","singleInteractionAnnouncement":"Interaction appeared:","multipleInteractionsAnnouncement":"Multiple interactions appeared.","videoPausedAnnouncement":"Video is paused"}}',
    ];

    protected Filesystem $disk;

    public function setUp(): void
    {
        parent::setUp();

        $this->disk = Storage::fake();
        config(['h5p.storage.path' => $this->disk->path("")]);
    }

    private function createVideo($contentId, $filename): void
    {
        $newFile = "content/$contentId/$filename";
        $fromFile = base_path('tests/files/sample.mp4');

        $this->disk->put($newFile, file_get_contents($fromFile));
        $this->disk->assertExists($newFile);
    }

    #[Test]
    public function adapterNotReady_throwException()
    {
        $adapter = $this->createMock(H5PVideoInterface::class);
        $adapter->method('isVideoReadyForStreaming')->willReturn(false);

        /** @var H5PContentsVideo|MockObject $contentVideo */
        $contentVideo = $this->createMock(H5PContentsVideo::class);

        $job = new PingVideoApi($contentVideo);
        $this->assertFalse($job->handle($adapter));
    }

    #[Test]
    public function adapterReady_noChildren_thensuccess()
    {
        $this->seed(TestH5PSeeder::class);

        $streamUrl = 'http://www.stream.url';
        $mimeType = 'video/unitTest';
        $adapter = $this->createMock(H5PVideoInterface::class);
        $adapter->method('isVideoReadyForStreaming')->willReturn(true);
        $adapter->method('getStreamingUrl')->willReturn($streamUrl);
        $adapter->method('getAdapterMimeType')->willReturn($mimeType);

        $packageStructure = $this->packageStructure['interactiveVideoWithLocalVideoSource'];
        $videoSource = 'videos/files-5a337db5cdf93.mp4';
        /** @var Collection<H5PContent> $h5pContents */
        $h5pContents = H5PContent::factory()->count(5)->create([
            'library_id' => 202,
            'parameters' => $packageStructure,
            'version_id' => $this->faker->unique()->uuid,
        ])->each(function (H5PContent $h5pContent) use ($videoSource) {
            $this->setupContentDirectories($h5pContent->id);
            $this->createVideo($h5pContent->id, $videoSource);
            $h5pContent
                ->contentVideos()
                ->save(H5PContentsVideo::factory()
                    ->create([
                        'h5p_content_id' => $h5pContent->id,
                        'source_file' => $videoSource,
                    ]));
        });

        $h5pContents->each(function ($h5p) use ($videoSource) {
            $this->disk->assertExists('content/' . $h5p->id . '/' . $videoSource);
        });

        /** @var H5PContent $h5pContent */
        $h5pContent = $h5pContents->random();
        /** @var H5PContentsVideo $contentVideo */
        $contentVideo = $h5pContent->contentVideos()->first();
        ContentVersion::factory()->create([
            'id' => $h5pContent->version_id,
            'content_id' => $h5pContent->id,
        ]);

        config(['h5p.video.enable' => true]);

        $job = new PingVideoApi($contentVideo);
        $job->handle($adapter);

        $packageStructure = json_decode($packageStructure);
        $packageStructure->interactiveVideo->video->files[0]->path = $streamUrl;
        $packageStructure->interactiveVideo->video->files[0]->mime = $mimeType;

        $this->assertDatabaseHas('h5p_contents', [
            'id' => $h5pContent->id,
            'parameters' => json_encode($packageStructure),
            'filtered' => '',
        ]);
        $this->disk->assertMissing('content/' . $h5pContent->id . '/' . $videoSource);
    }

    #[Test]
    public function adapterReady_noVideo_thenFail()
    {
        $streamUrl = 'http://www.stream.url';
        $adapter = $this->createMock(H5PVideoInterface::class);
        $adapter->method('isVideoReadyForStreaming')->willReturn(true);
        $adapter->method('getStreamingUrl')->willReturn($streamUrl);

        $contentVideo = new H5PContentsVideo();

        $job = new PingVideoApi($contentVideo);
        $this->assertFalse($job->handle($adapter));
    }

    #[Test]
    public function adapterReady_oneChild_thenSuccess()
    {
        $this->seed(TestH5PSeeder::class);

        $streamUrl = 'http://www.stream.url';
        $mimeType = 'video/unitTest';

        $adapter = $this->createMock(H5PVideoInterface::class);
        $adapter->method('isVideoReadyForStreaming')->willReturn(true);
        $adapter->method('getStreamingUrl')->willReturn($streamUrl);
        $adapter->method('getAdapterMimeType')->willReturn($mimeType);

        $packageStructure = $this->packageStructure['interactiveVideoWithLocalVideoSource'];
        $h5pContentParent = H5PContent::factory()->create([
            'library_id' => 202,
            'parameters' => $packageStructure,
            'version_id' => $this->faker->unique()->uuid,
        ]);
        $this->setupContentDirectories($h5pContentParent->id);
        $videoSource = 'videos/files-5a337db5cdf93.mp4';
        $this->createVideo($h5pContentParent->id, $videoSource);
        $h5pContentParent->contentVideos()
            ->save(
                H5PContentsVideo::factory()
                    ->create([
                        'h5p_content_id' => $h5pContentParent->id,
                        'source_file' => $videoSource,
                    ]),
            );

        $packageStructureParent = json_decode($packageStructure);
        $packageStructureChild = json_decode($packageStructure);
        $packageStructureParent->interactiveVideo->video->files[0]->path = $streamUrl;
        $packageStructureParent->interactiveVideo->video->files[0]->mime = $mimeType;

        $packageStructureChild->interactiveVideo->unitTestValue = true;
        $h5pContentChild = H5PContent::factory()->create([
            'library_id' => 202,
            'parameters' => json_encode($packageStructureChild),
            'version_id' => $this->faker->unique()->uuid,
        ]);
        $this->setupContentDirectories($h5pContentChild->id);
        $this->createVideo($h5pContentChild->id, $videoSource);

        ContentVersion::factory()->create([
            'id' => $h5pContentParent->version_id,
            'content_id' => $h5pContentParent->id,
        ]);
        ContentVersion::factory()->create([
            'id' => $h5pContentChild->version_id,
            'content_id' => $h5pContentChild->id,
            'parent_id' => $h5pContentParent->version_id,
        ]);

        $packageStructureChild->interactiveVideo->video->files[0]->path = $streamUrl;
        $packageStructureChild->interactiveVideo->video->files[0]->mime = $mimeType;

        /** @var H5PContentsVideo $contentVideo */
        $contentVideo = $h5pContentParent->contentVideos()->first();

        config(['h5p.video.enable' => true]);

        $job = new PingVideoApi($contentVideo);
        $job->handle($adapter);

        $this->assertDatabaseHas('h5p_contents', [
            'id' => $h5pContentParent->id,
            'parameters' => json_encode($packageStructureParent),
            'filtered' => '',
        ]);
        $this->assertDatabaseHas('h5p_contents', [
            'id' => $h5pContentChild->id,
            'parameters' => json_encode($packageStructureChild),
            'filtered' => '',
        ]);

        $this->disk->assertMissing('content/' . $h5pContentParent->id . '/' . $videoSource);
        $this->disk->assertMissing('content/' . $h5pContentChild->id . '/' . $videoSource);

        $this->assertEquals(1, $job->processedChildren);
    }

    private function createVideoData(H5PContent $content, $videoSource): H5PContent
    {
        $this->setupContentDirectories($content->id);
        $this->createVideo($content->id, $videoSource);
        $content->contentVideos()
            ->save(
                H5PContentsVideo::factory()
                    ->create([
                        'h5p_content_id' => $content->id,
                        'source_file' => $videoSource,
                    ]),
            );
        return $content;
    }

    #[Test]
    public function adapterReady_withGrandchildren_thenSuccess()
    {
        $this->seed(TestH5PSeeder::class);

        $streamUrl = 'http://www.stream.url';
        $mimeType = 'video/unitTest';

        $adapter = $this->createMock(H5PVideoInterface::class);
        $adapter->method('isVideoReadyForStreaming')->willReturn(true);
        $adapter->method('getStreamingUrl')->willReturn($streamUrl);
        $adapter->method('getAdapterMimeType')->willReturn($mimeType);

        $packageStructure = $this->packageStructure['interactiveVideoWithLocalVideoSource'];
        $h5pContents = H5PContent::factory()->count(5)->create([
            'library_id' => 202,
            'parameters' => $packageStructure,
            'version_id' => $this->faker->unique()->uuid,
        ]);

        $this->createVideoData($h5pContents->first(), 'videos/files-dummy.mp4');
        $videoSource = 'videos/files-5a337db5cdf93.mp4';
        $h5pContentParent = $this->createVideoData($h5pContents->get(2), $videoSource);

        $packageStructureChild = json_decode($packageStructure);
        $packageStructureChild->interactiveVideo->unitTestValue = true;
        $h5pContentChild = H5PContent::factory()->create([
            'library_id' => 202,
            'parameters' => json_encode($packageStructureChild),
            'version_id' => $this->faker->unique()->uuid,
        ]);
        $this->setupContentDirectories($h5pContentChild->id);
        $this->createVideo($h5pContentChild->id, $videoSource);

        $packageStructureGrandchild = $packageStructureChild;
        $packageStructureGrandchild->interactiveVideo->unitTestValue = false;

        $h5pContentGrandchild = H5PContent::factory()->create([
            'library_id' => 202,
            'parameters' => json_encode($packageStructureGrandchild),
            'version_id' => $this->faker->unique()->uuid,
        ]);
        $this->setupContentDirectories($h5pContentGrandchild->id);
        $this->createVideo($h5pContentGrandchild->id, $videoSource);

        ContentVersion::factory()->create([
            'id' => $h5pContentParent->version_id,
            'content_id' => $h5pContentParent->id,
        ]);
        ContentVersion::factory()->create([
            'id' => $h5pContentChild->version_id,
            'content_id' => $h5pContentChild->id,
            'parent_id' => $h5pContentParent->version_id,
        ]);
        ContentVersion::factory()->create([
            'id' => $h5pContentGrandchild->version_id,
            'content_id' => $h5pContentGrandchild->id,
            'parent_id' => $h5pContentChild->version_id,
        ]);

        /** @var H5PContentsVideo $contentVideo */
        $contentVideo = $h5pContentParent->contentVideos()->first();

        config(['h5p.video.enable' => true]);

        $job = new PingVideoApi($contentVideo);
        $job->handle($adapter);

        $packageStructureGrandchild->interactiveVideo->video->files[0]->path = $streamUrl;
        $packageStructureGrandchild->interactiveVideo->video->files[0]->mime = $mimeType;

        $this->assertDatabaseHas('h5p_contents', [
            'id' => $h5pContentGrandchild->id,
            'parameters' => json_encode($packageStructureGrandchild),
            'filtered' => '',
        ]);

        $this->assertEquals(2, $job->processedChildren);

        $packageStructureGrandchild->interactiveVideo->unitTestValue = true;
        $this->assertDatabaseHas('h5p_contents', [
            'id' => $h5pContentChild->id,
            'parameters' => json_encode($packageStructureGrandchild),
            'filtered' => '',
        ]);

        unset($packageStructureGrandchild->interactiveVideo->unitTestValue);
        $this->assertDatabaseHas('h5p_contents', [
            'id' => $h5pContentParent->id,
            'parameters' => json_encode($packageStructureGrandchild),
            'filtered' => '',
        ]);

        $this->disk->assertMissing('content/' . $h5pContentParent->id . '/' . $videoSource);
        $this->disk->assertMissing('content/' . $h5pContentChild->id . '/' . $videoSource);
        $this->disk->assertMissing('content/' . $h5pContentGrandchild->id . '/' . $videoSource);
    }

    #[Test]
    public function adapterReady_withGrandchildrenAndDifferentSource_thenSuccess()
    {
        $this->seed(TestH5PSeeder::class);

        $streamUrl = 'http://www.stream.url';
        $mimeType = 'video/unitTest';

        $adapter = $this->createMock(H5PVideoInterface::class);
        $adapter->method('isVideoReadyForStreaming')->willReturn(true);
        $adapter->method('getStreamingUrl')->willReturn($streamUrl);
        $adapter->method('getAdapterMimeType')->willReturn($mimeType);

        $packageStructure = $this->packageStructure['interactiveVideoWithLocalVideoSource'];
        $h5pContentParent = H5PContent::factory()->create([
            'library_id' => 202,
            'parameters' => $packageStructure,
            'version_id' => $this->faker->unique()->uuid,
        ]);
        $videoSource = 'videos/files-5a337db5cdf93.mp4';
        $this->setupContentDirectories($h5pContentParent->id);
        $this->createVideo($h5pContentParent->id, $videoSource);

        $h5pContentParent->contentVideos()
            ->save(
                H5PContentsVideo::factory()
                    ->create([
                        'h5p_content_id' => $h5pContentParent->id,
                        'source_file' => $videoSource,
                    ]),
            );

        $packageStructureChild = json_decode($packageStructure);
        $packageStructureChild->interactiveVideo->unitTestValue = true;
        $h5pContentChild = H5PContent::factory()->create([
            'library_id' => 202,
            'parameters' => json_encode($packageStructureChild),
            'version_id' => $this->faker->unique()->uuid,
        ]);
        $this->setupContentDirectories($h5pContentChild->id);
        $this->createVideo($h5pContentChild->id, $videoSource);

        $packageStructureGrandchild = unserialize(serialize($packageStructureChild));
        $packageStructureGrandchild->interactiveVideo->unitTestValue = false;

        $newFileId = 'videos/files-99937db5cd666.mp4';
        $packageStructureGrandchild->interactiveVideo->video->files[0]->path = $newFileId;
        $h5pContentGrandchild = H5PContent::factory()->create([
            'library_id' => 202,
            'parameters' => json_encode($packageStructureGrandchild),
            'version_id' => $this->faker->unique()->uuid,
        ]);
        $this->setupContentDirectories($h5pContentGrandchild->id);
        $this->createVideo($h5pContentGrandchild->id, $newFileId);
        $h5pContentGrandchild->contentVideos()
            ->save(
                H5PContentsVideo::factory()
                    ->create([
                        'h5p_content_id' => $h5pContentGrandchild->id,
                        'source_file' => $newFileId,
                    ]),
            );

        ContentVersion::factory()->create([
            'id' => $h5pContentParent->version_id,
            'content_id' => $h5pContentParent->id,
        ]);
        ContentVersion::factory()->create([
            'id' => $h5pContentChild->version_id,
            'content_id' => $h5pContentChild->id,
            'parent_id' => $h5pContentParent->version_id,
        ]);
        ContentVersion::factory()->create([
            'id' => $h5pContentGrandchild->version_id,
            'content_id' => $h5pContentGrandchild->id,
            'parent_id' => $h5pContentChild->version_id,
        ]);

        /** @var H5PContentsVideo $contentVideo */
        $contentVideo = $h5pContentParent->contentVideos()->first();

        config(['h5p.video.enable' => true]);

        $job = new PingVideoApi($contentVideo);
        $job->handle($adapter);

        $this->assertDatabaseHas('h5p_contents', [
            'id' => $h5pContentGrandchild->id,
            'parameters' => json_encode($packageStructureGrandchild),
            'filtered' => $h5pContentGrandchild->filtered,
        ]);

        $this->disk->assertMissing('content/' . $h5pContentParent->id . '/' . $videoSource);
        $this->disk->assertMissing('content/' . $h5pContentChild->id . '/' . $videoSource);
        $this->disk->assertExists('content/' . $h5pContentGrandchild->id . '/' . $newFileId);

        $this->assertEquals(1, $job->processedChildren);
    }

    #[Test]
    public function adapterReady_noVideoFilesInJson_thensuccess()
    {
        $this->seed(TestH5PSeeder::class);

        $streamUrl = 'http://www.stream.url';
        $mimeType = 'video/unitTest';
        $adapter = $this->createMock(H5PVideoInterface::class);
        $adapter->method('isVideoReadyForStreaming')->willReturn(true);
        $adapter->method('getStreamingUrl')->willReturn($streamUrl);
        $adapter->method('getAdapterMimeType')->willReturn($mimeType);

        $packageStructure = $this->packageStructure['interactiveVideoWithNoLocalVideoFiles'];
        $videoSource = 'videos/files-5a337db5cdf93.mp4';
        /** @var Collection<H5PContent> $h5pContents */
        $h5pContents = H5PContent::factory()->count(5)->create([
            'library_id' => 202,
            'parameters' => $packageStructure,
        ])->each(function (H5PContent $h5pContent) use ($videoSource) {
            $this->setupContentDirectories($h5pContent->id);
            $this->createVideo($h5pContent->id, $videoSource);
            $h5pContent
                ->contentVideos()
                ->save(H5PContentsVideo::factory()
                    ->create([
                        'h5p_content_id' => $h5pContent->id,
                        'source_file' => $videoSource,
                    ]));
        });

        /** @var H5PContent $h5pContent */
        $h5pContent = $h5pContents->random();
        /** @var H5PContentsVideo $contentVideo */
        $contentVideo = $h5pContent->contentVideos()->first();

        $job = new PingVideoApi($contentVideo);
        $job->handle($adapter);

        $packageStructure = json_decode($packageStructure);
        $packageStructure->interactiveVideo->video->files = [(object) ["path" => $streamUrl, 'mime' => $mimeType]];

        $this->assertDatabaseHas('h5p_contents', [
            'id' => $h5pContent->id,
            'parameters' => json_encode($packageStructure),
            'filtered' => '',
        ]);
        $this->disk->assertMissing('content/' . $h5pContent->id . '/' . $videoSource);
    }

    #[Test]
    public function adapterReady_noLocalFiles_thensuccess()
    {
        $this->seed(TestH5PSeeder::class);

        $streamUrl = 'http://www.stream.url';
        $mimeType = 'video/unitTest';
        $adapter = $this->createMock(H5PVideoInterface::class);
        $adapter->method('isVideoReadyForStreaming')->willReturn(true);
        $adapter->method('getStreamingUrl')->willReturn($streamUrl);
        $adapter->method('getAdapterMimeType')->willReturn($mimeType);

        $packageStructure = $this->packageStructure['interactiveVideoWithNoLocalVideoFiles'];
        $videoSource = 'videos/files-5a337db5cdf93.mp4';
        $h5pContent = H5PContent::factory()->create([
            'library_id' => 202,
            'parameters' => $packageStructure,
        ]);
        $this->setupContentDirectories($h5pContent->id);
        $this->createVideo($h5pContent->id, $videoSource);
        $h5pContent
            ->contentVideos()
            ->save(H5PContentsVideo::factory()
                ->create([
                    'h5p_content_id' => $h5pContent->id,
                    'source_file' => '',
                ]));

        /** @var H5PContentsVideo $contentVideo */
        $contentVideo = $h5pContent->contentVideos()->first();

        $job = new PingVideoApi($contentVideo);
        $this->assertFalse($job->handle($adapter));

        $this->assertDatabaseHas('h5p_contents', [
            'id' => $h5pContent->id,
            'parameters' => $packageStructure,
        ]);
        $this->disk->assertExists('content/' . $h5pContent->id . '/' . $videoSource);
    }
}
