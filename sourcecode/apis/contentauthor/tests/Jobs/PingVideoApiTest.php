<?php

namespace Tests\Jobs;

use App\Libraries\ContentAuthorStorage;
use Faker\Factory;
use App\H5PContent;
use Tests\TestCase;
use App\H5PContentsVideo;
use App\Jobs\PingVideoApi;
use Tests\db\TestH5PSeeder;
use Tests\Traits\ContentAuthorStorageTrait;
use Tests\Traits\VersionedH5PTrait;
use Cerpus\VersionClient\VersionData;
use Tests\Traits\MockVersioningTrait;
use Cerpus\VersionClient\VersionClient;
use Illuminate\Support\Facades\Storage;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Libraries\H5P\Interfaces\H5PVideoInterface;
use Illuminate\Foundation\Testing\Concerns\InteractsWithDatabase;
use Tests\Traits\WithFaker;

class PingVideoApiTest extends TestCase
{
    use RefreshDatabase, MockVersioningTrait, VersionedH5PTrait, InteractsWithDatabase, WithFaker, ContentAuthorStorageTrait;

    private $packageStructure = [
        'interactiveVideoWithLocalVideoSource' => '{"interactiveVideo":{"video":{"startScreenOptions":{"title":"Interactive Video","hideStartTitle":false,"copyright":""},"textTracks":[{"label":"Subtitles","kind":"subtitles","srcLang":"en"}],"files":[{"path":"videos\/files-5a337db5cdf93.mp4#tmp","mime":"video\/mp4","copyright":{"license":"U"}}]},"assets":{"interactions":[],"bookmarks":[]},"summary":{"task":{"library":"H5P.Summary 1.8","params":{"intro":"Choose the correct statement.","summaries":[{"subContentId":"1dd50eaf-d839-43eb-b41a-091a7f39874d","tip":""}],"overallFeedback":[{"from":0,"to":100}],"solvedLabel":"Progress:","scoreLabel":"Wrong answers:","resultLabel":"Your result","labelCorrect":"Correct.","labelIncorrect":"Incorrect! Please try again.","labelCorrectAnswers":"Correct answers."},"subContentId":"37b2670f-e199-4f76-bd33-0d6ea81efcce"},"displayAt":3}},"override":{"autoplay":false,"loop":false,"showBookmarksmenuOnLoad":false,"showRewind10":false,"preventSkipping":false,"deactivateSound":false},"l10n":{"interaction":"Interaction","play":"Play","pause":"Pause","mute":"Mute","unmute":"Unmute","quality":"Video Quality","captions":"Captions","close":"Close","fullscreen":"Fullscreen","exitFullscreen":"Exit Fullscreen","summary":"Summary","bookmarks":"Bookmarks","defaultAdaptivitySeekLabel":"Continue","continueWithVideo":"Continue with video","playbackRate":"Playback Rate","rewind10":"Rewind 10 Seconds","navDisabled":"Navigation is disabled","sndDisabled":"Sound is disabled","requiresCompletionWarning":"You need to answer all the questions correctly before continuing.","back":"Back","hours":"Hours","minutes":"Minutes","seconds":"Seconds","currentTime":"Current time:","totalTime":"Total time:","navigationHotkeyInstructions":"Use key k for starting and stopping video at any time","singleInteractionAnnouncement":"Interaction appeared:","multipleInteractionsAnnouncement":"Multiple interactions appeared.","videoPausedAnnouncement":"Video is paused"}}',
        'interactiveVideoWithNoLocalVideoFiles' => '{"interactiveVideo":{"video":{"startScreenOptions":{"title":"Interactive Video","hideStartTitle":false,"copyright":""},"textTracks":[{"label":"Subtitles","kind":"subtitles","srcLang":"en"}],"files":[]},"assets":{"interactions":[],"bookmarks":[]},"summary":{"task":{"library":"H5P.Summary 1.8","params":{"intro":"Choose the correct statement.","summaries":[{"subContentId":"1dd50eaf-d839-43eb-b41a-091a7f39874d","tip":""}],"overallFeedback":[{"from":0,"to":100}],"solvedLabel":"Progress:","scoreLabel":"Wrong answers:","resultLabel":"Your result","labelCorrect":"Correct.","labelIncorrect":"Incorrect! Please try again.","labelCorrectAnswers":"Correct answers."},"subContentId":"37b2670f-e199-4f76-bd33-0d6ea81efcce"},"displayAt":3}},"override":{"autoplay":false,"loop":false,"showBookmarksmenuOnLoad":false,"showRewind10":false,"preventSkipping":false,"deactivateSound":false},"l10n":{"interaction":"Interaction","play":"Play","pause":"Pause","mute":"Mute","unmute":"Unmute","quality":"Video Quality","captions":"Captions","close":"Close","fullscreen":"Fullscreen","exitFullscreen":"Exit Fullscreen","summary":"Summary","bookmarks":"Bookmarks","defaultAdaptivitySeekLabel":"Continue","continueWithVideo":"Continue with video","playbackRate":"Playback Rate","rewind10":"Rewind 10 Seconds","navDisabled":"Navigation is disabled","sndDisabled":"Sound is disabled","requiresCompletionWarning":"You need to answer all the questions correctly before continuing.","back":"Back","hours":"Hours","minutes":"Minutes","seconds":"Seconds","currentTime":"Current time:","totalTime":"Total time:","navigationHotkeyInstructions":"Use key k for starting and stopping video at any time","singleInteractionAnnouncement":"Interaction appeared:","multipleInteractionsAnnouncement":"Multiple interactions appeared.","videoPausedAnnouncement":"Video is paused"}}',
    ];

    /** @var FilesystemAdapter */
    protected $disk;

    public function setUp(): void
    {
        parent::setUp();
        $this->setUpContentAuthorStorage();

        $this->disk = Storage::fake($this->contentAuthorStorage->getBucketDiskName());
        config(['h5p.storage.path' => $this->disk->path("")]);
    }

    private function createVideo($contentId, $filename)
    {
        $newFile = 'content' . DIRECTORY_SEPARATOR . $contentId . DIRECTORY_SEPARATOR . $filename;
        $fromFile = implode(DIRECTORY_SEPARATOR, [
            base_path(),
            'tests',
            'files',
            'sample.mp4'
        ]);

        $this->disk->put($newFile, file_get_contents($fromFile));
        $this->disk->assertExists($newFile);
    }

    /**
     * @test
     */
    public function adapterNotReady_throwException()
    {
        $adapter = $this->createMock(H5PVideoInterface::class);
        $adapter->method('isVideoReadyForStreaming')->willReturn(false);

        /** @var H5PContentsVideo $contentVideo */
        $contentVideo = $this->createMock(H5PContentsVideo::class);
        $versionClient = new VersionClient();

        $job = new PingVideoApi($contentVideo, $versionClient);
        $this->assertFalse($job->handle($adapter));
    }

    /**
     * @test
     */
    public function adapterReady_noChildren_thensuccess()
    {
        $this->seed(TestH5PSeeder::class);

        $this->setupVersion([
            'getVersion' => $this->getVersionData(),
        ]);

        $streamUrl = 'http://www.stream.url';
        $mimeType = 'video/unitTest';
        $adapter = $this->createMock(H5PVideoInterface::class);
        $adapter->method('isVideoReadyForStreaming')->willReturn(true);
        $adapter->method('getStreamingUrl')->willReturn($streamUrl);
        $adapter->method('getAdapterMimeType')->willReturn($mimeType);

        $packageStructure = $this->packageStructure['interactiveVideoWithLocalVideoSource'];
        $videoSource = 'videos/files-5a337db5cdf93.mp4';
        $h5pContents = H5PContent::factory()->count(5)->create([
            'library_id' => 202,
            'parameters' => $packageStructure,
        ])->each(function ($h5pContent) use ($videoSource) {
            $this->setupContentDirectories($h5pContent->id);
            $this->createVideo($h5pContent->id, $videoSource);
            /** @var H5PContent $h5pContent */
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

        $h5pContent = $h5pContents->random();
        $contentVideo = $h5pContent->contentVideos()->first();
        $versionClient = app(VersionClient::class);

        config(['h5p.video.enable' => true]);

        $job = new PingVideoApi($contentVideo, $versionClient);
        $job->handle($adapter);

        $packageStructure = json_decode($packageStructure);
        $packageStructure->interactiveVideo->video->files[0]->path = $streamUrl;
        $packageStructure->interactiveVideo->video->files[0]->mime = $mimeType;

        $this->assertDatabaseHas('h5p_contents', [
            'id' => $h5pContent->id,
            'parameters' => json_encode($packageStructure),
            'filtered' => ''
        ]);
        $this->disk->assertMissing('content/' . $h5pContent->id . '/' . $videoSource);
    }

    private function getVersionData(array $values = null)
    {
        $faker = Factory::create();
        $versionData = new VersionData();
        $data = [
            'externalReference' => $faker->unique()->uuid,
            'externalUrl' => $faker->url,
            'externalSystem' => str_replace(" ", "", $faker->company),
            'id' => $faker->unique()->uuid,
            'parent' => null,
            'children' => null,
            'versionPurpose' => 'create',
            'userId' => $faker->unique()->uuid,
            'createdAt' => $faker->unixTime,
        ];

        if (is_array($values)) {
            $data = array_merge($data, $values);
        }

        $versionData->populate((object)$data);
        return $versionData;
    }

    /**
     * @test
     */
    public function adapterReady_noVideo_thenFail()
    {
        $streamUrl = 'http://www.stream.url';
        $adapter = $this->createMock(H5PVideoInterface::class);
        $adapter->method('isVideoReadyForStreaming')->willReturn(true);
        $adapter->method('getStreamingUrl')->willReturn($streamUrl);

        $contentVideo = new H5PContentsVideo();
        $versionClient = new VersionClient();

        $job = new PingVideoApi($contentVideo, $versionClient);
        $this->assertFalse($job->handle($adapter));
    }

    /**
     * @test
     */
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
            ->save(H5PContentsVideo::factory()
                ->create([
                    'h5p_content_id' => $h5pContentParent->id,
                    'source_file' => $videoSource,
                ])
            );

        $packageStructureParent = json_decode($packageStructure);
        $packageStructureChild = json_decode($packageStructure);
        $packageStructureParent->interactiveVideo->video->files[0]->path = $streamUrl;
        $packageStructureParent->interactiveVideo->video->files[0]->mime = $mimeType;

        $packageStructureChild->interactiveVideo->unitTestValue = true;
        $h5pContentChild = H5PContent::factory()->create([
            'library_id' => 202,
            'parameters' => json_encode($packageStructureChild),
        ]);
        $this->setupContentDirectories($h5pContentChild->id);
        $this->createVideo($h5pContentChild->id, $videoSource);

        $this->setupVersion([
            'getVersion' => $this->getVersionData([
                'children' => [
                    $this->getVersionData([
                        'externalReference' => $h5pContentChild->id
                    ])
                ],
                'versionPurpose' => 'update',
                'externalReference' => $h5pContentParent->id
            ]),
        ]);

        $packageStructureChild->interactiveVideo->video->files[0]->path = $streamUrl;
        $packageStructureChild->interactiveVideo->video->files[0]->mime = $mimeType;

        $contentVideo = $h5pContentParent->contentVideos()->first();
        $versionClient = app(VersionClient::class);

        config(['h5p.video.enable' => true]);

        $job = new PingVideoApi($contentVideo, $versionClient);
        $job->handle($adapter);

        $this->assertDatabaseHas('h5p_contents', [
            'id' => $h5pContentParent->id,
            'parameters' => json_encode($packageStructureParent),
            'filtered' => '',
        ]);
        $this->assertDatabaseHas('h5p_contents', [
            'id' => $h5pContentChild->id,
            'parameters' => json_encode($packageStructureChild),
            'filtered' => ''
        ]);

        $this->disk->assertMissing('content/' . $h5pContentParent->id . '/' . $videoSource);
        $this->disk->assertMissing('content/' . $h5pContentChild->id . '/' . $videoSource);

        $this->assertEquals(1, $job->processedChildren);
    }

    private function createVideoData(H5PContent $content, $videoSource)
    {
        $this->setupContentDirectories($content->id);
        $this->createVideo($content->id, $videoSource);
        $content->contentVideos()
            ->save(H5PContentsVideo::factory()
                ->create([
                    'h5p_content_id' => $content->id,
                    'source_file' => $videoSource,
                ])
            );
        return $content;
    }

    /**
     * @test
     */
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
        $h5pContents =H5PContent::factory()->count(5)->create([
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
        ]);
        $this->setupContentDirectories($h5pContentChild->id);
        $this->createVideo($h5pContentChild->id, $videoSource);

        $packageStructureGrandchild = $packageStructureChild;
        $packageStructureGrandchild->interactiveVideo->unitTestValue = false;

        $h5pContentGrandchild = H5PContent::factory()->create([
            'library_id' => 202,
            'parameters' => json_encode($packageStructureGrandchild),
        ]);
        $this->setupContentDirectories($h5pContentGrandchild->id);
        $this->createVideo($h5pContentGrandchild->id, $videoSource);

        $this->setupVersion([
            'getVersion' => $this->getVersionData([
                'externalReference' => $h5pContentParent->id,
                'children' => [
                    $this->getVersionData([
                        'externalReference' => $h5pContentChild->id,
                        'versionPurpose' => 'update',
                        'children' => [
                            $this->getVersionData([
                                'externalReference' => $h5pContentGrandchild->id,
                                'versionPurpose' => 'update',
                            ])
                        ]
                    ])
                ],
            ]),
        ]);

        $contentVideo = $h5pContentParent->contentVideos()->first();
        $versionClient = app(VersionClient::class);

        config(['h5p.video.enable' => true]);

        $job = new PingVideoApi($contentVideo, $versionClient);
        $job->handle($adapter);

        $packageStructureGrandchild->interactiveVideo->video->files[0]->path = $streamUrl;
        $packageStructureGrandchild->interactiveVideo->video->files[0]->mime = $mimeType;

        $this->assertDatabaseHas('h5p_contents', [
            'id' => $h5pContentGrandchild->id,
            'parameters' => json_encode($packageStructureGrandchild),
            'filtered' => ''
        ]);

        $this->assertEquals(2, $job->processedChildren);

        $packageStructureGrandchild->interactiveVideo->unitTestValue = true;
        $this->assertDatabaseHas('h5p_contents', [
            'id' => $h5pContentChild->id,
            'parameters' => json_encode($packageStructureGrandchild),
            'filtered' => ''
        ]);

        unset($packageStructureGrandchild->interactiveVideo->unitTestValue);
        $this->assertDatabaseHas('h5p_contents', [
            'id' => $h5pContentParent->id,
            'parameters' => json_encode($packageStructureGrandchild),
            'filtered' => ''
        ]);

        $this->disk->assertMissing('content/' . $h5pContentParent->id . '/' . $videoSource);
        $this->disk->assertMissing('content/' . $h5pContentChild->id . '/' . $videoSource);
        $this->disk->assertMissing('content/' . $h5pContentGrandchild->id . '/' . $videoSource);
    }

    /**
     * @test
     */
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
            ->save(H5PContentsVideo::factory()
                ->create([
                    'h5p_content_id' => $h5pContentParent->id,
                    'source_file' => $videoSource,
                ])
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
            ->save(H5PContentsVideo::factory()
                ->create([
                    'h5p_content_id' => $h5pContentGrandchild->id,
                    'source_file' => $newFileId,
                ])
            );

        $this->setupVersion([
            'getVersion' => $this->getVersionData([
                'externalReference' => $h5pContentParent->id,
                'children' => [
                    $this->getVersionData([
                        'externalReference' => $h5pContentChild->id,
                        'versionPurpose' => 'update',
                        'children' => [
                            $this->getVersionData([
                                'externalReference' => $h5pContentGrandchild->id,
                                'versionPurpose' => 'update',
                            ])
                        ]
                    ])
                ],
            ]),
        ]);

        $contentVideo = $h5pContentParent->contentVideos()->first();
        $versionClient = app(VersionClient::class);

        config(['h5p.video.enable' => true]);

        $job = new PingVideoApi($contentVideo, $versionClient);
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

    /**
     * @test
     */
    public function adapterReady_noVideoFilesInJson_thensuccess()
    {
        $this->seed(TestH5PSeeder::class);

        $this->setupVersion([
            'getVersion' => $this->getVersionData(),
        ]);

        $streamUrl = 'http://www.stream.url';
        $mimeType = 'video/unitTest';
        $adapter = $this->createMock(H5PVideoInterface::class);
        $adapter->method('isVideoReadyForStreaming')->willReturn(true);
        $adapter->method('getStreamingUrl')->willReturn($streamUrl);
        $adapter->method('getAdapterMimeType')->willReturn($mimeType);

        $packageStructure = $this->packageStructure['interactiveVideoWithNoLocalVideoFiles'];
        $videoSource = 'videos/files-5a337db5cdf93.mp4';
        $h5pContents =H5PContent::factory()->count(5)->create([
            'library_id' => 202,
            'parameters' => $packageStructure,
        ])->each(function ($h5pContent) use ($videoSource) {
            $this->setupContentDirectories($h5pContent->id);
            $this->createVideo($h5pContent->id, $videoSource);
            /** @var H5PContent $h5pContent */
            $h5pContent
                ->contentVideos()
                ->save(H5PContentsVideo::factory()
                    ->create([
                        'h5p_content_id' => $h5pContent->id,
                        'source_file' => $videoSource,
                    ]));
        });

        $h5pContent = $h5pContents->random();
        $contentVideo = $h5pContent->contentVideos()->first();
        $versionClient = app(VersionClient::class);

        $job = new PingVideoApi($contentVideo, $versionClient);
        $job->handle($adapter);

        $packageStructure = json_decode($packageStructure);
        $packageStructure->interactiveVideo->video->files = [(object)["path" => $streamUrl, 'mime' => $mimeType]];

        $this->assertDatabaseHas('h5p_contents', [
            'id' => $h5pContent->id,
            'parameters' => json_encode($packageStructure),
            'filtered' => ''
        ]);
        $this->disk->assertMissing('content/' . $h5pContent->id . '/' . $videoSource);
    }

    /**
     * @test
     */
    public function adapterReady_noLocalFiles_thensuccess()
    {
        $this->seed(TestH5PSeeder::class);

        $this->setupVersion([
            'getVersion' => $this->getVersionData(),
        ]);

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
        /** @var H5PContent $h5pContent */
        $h5pContent
            ->contentVideos()
            ->save(H5PContentsVideo::factory()
                ->create([
                    'h5p_content_id' => $h5pContent->id,
                    'source_file' => '',
                ]));

        /** @var H5PContentsVideo $contentVideo */
        $contentVideo = $h5pContent->contentVideos()->first();
        $versionClient = app(VersionClient::class);

        $job = new PingVideoApi($contentVideo, $versionClient);
        $this->assertFalse($job->handle($adapter));

        $this->assertDatabaseHas('h5p_contents', [
            'id' => $h5pContent->id,
            'parameters' => $packageStructure,
        ]);
        $this->disk->assertExists('content/' . $h5pContent->id . '/' . $videoSource);
    }

}
