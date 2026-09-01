<?php

namespace Tests\Integration\Libraries\H5P;

use App\H5PContent;
use App\H5PLibrary;
use App\Libraries\DataObjects\ContentStorageSettings;
use App\Libraries\H5P\Dataobjects\H5PAlterParametersSettingsDataObject;
use App\Libraries\H5P\Exceptions\H5PExternalMediaException;
use App\Libraries\H5P\H5PExport;
use App\Libraries\H5P\Interfaces\H5PExternalProviderInterface;
use App\Libraries\H5P\Interfaces\H5PImageInterface;
use Exception;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Tests\Helpers\MockH5PAdapterInterface;
use Tests\Seeds\TestH5PSeeder;
use Tests\TestCase;
use ZipArchive;

class H5PExportTest extends TestCase
{
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
        symlink($this->testDisk->path('files/libraries'), $this->exportDisk->path('libraries'));
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        unlink($this->exportDisk->path('libraries'));
    }

    /**
     * @throws Exception
     */
    #[DataProvider('provider_noMultimedia')]
    #[Test]
    public function noMultimedia(bool $usePatchFolder)
    {
        $this->setupH5PAdapter([
            'alterLibrarySemantics' => null,
        ]);

        $this->seed(TestH5PSeeder::class);

        if ($usePatchFolder) {
            $lib = H5PLibrary::find(284);
            $lib->minor_version = 14;
            $lib->patch_version = 6;
            $lib->patch_version_in_folder_name = true;
            $lib->save();
        }

        $params = '{"text":"<p>Fill in the missing words<\/p>\n","overallFeedback":[{"from":0,"to":100}],"showSolutions":"Show solution","tryAgain":"Retry","checkAnswer":"Check","notFilledOut":"Please fill in all blanks to view solution","answerIsCorrect":"\':ans\' is correct","answerIsWrong":"\':ans\' is wrong","answeredCorrectly":"Answered correctly","answeredIncorrectly":"Answered incorrectly","solutionLabel":"Correct answer:","inputLabel":"Blank input @num of @total","inputHasTipLabel":"Tip available","tipLabel":"Tip","behaviour":{"enableRetry":true,"enableSolutionsButton":true,"enableCheckButton":true,"autoCheck":false,"caseSensitive":true,"showSolutionsRequiresInput":true,"separateLines":false,"disableImageZooming":false,"confirmCheckDialog":false,"confirmRetryDialog":false,"acceptSpellingErrors":false},"scoreBarLabel":"You got :num out of :total points","confirmCheck":{"header":"Finish ?","body":"Are you sure you wish to finish ?","cancelLabel":"Cancel","confirmLabel":"Finish"},"confirmRetry":{"header":"Retry ?","body":"Are you sure you wish to retry ?","cancelLabel":"Cancel","confirmLabel":"Confirm"},"questions":["<p>*Fishing* is a fun *activity*.<\/p>\n"]}';
        $h5p = H5PContent::factory()->create([
            'parameters' => $params,
            'library_id' => 284,
        ]);

        config(['feature.export_h5p_with_local_files' => false]);
        $export = resolve(H5PExport::class);
        $this->assertTrue($export->generateExport($h5p));
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

    public static function provider_noMultimedia(): \Generator
    {
        yield 'minorFolder' => [false];
        yield 'patchFolder' => [true];
    }

    /**
     * @throws Exception
     */
    #[Test]
    public function withLocalImage()
    {
        $this->setupH5PAdapter([
            'alterLibrarySemantics' => null,
        ]);

        $this->seed(TestH5PSeeder::class);
        $params = '{"media":{"params":{"contentName":"Image","file":{"path":"images\/file-5f6ca98160e6c.jpg","mime":"image\/jpeg","copyright":{"license":"U"},"width":196,"height":358}},"library":"H5P.Image 1.1","metadata":{"contentType":"Image","license":"U","title":"Untitled Image","authors":[],"changes":[],"extraTitle":"Untitled Image"},"subContentId":"ca86c100-d25c-4e19-ac6b-f50f843da292"},"text":"Fill in the missing words","overallFeedback":[{"from":0,"to":100}],"showSolutions":"Show solution","tryAgain":"Retry","checkAnswer":"Check","notFilledOut":"Please fill in all blanks to view solution","answerIsCorrect":"&#039;:ans&#039; is correct","answerIsWrong":"&#039;:ans&#039; is wrong","answeredCorrectly":"Answered correctly","answeredIncorrectly":"Answered incorrectly","solutionLabel":"Correct answer:","inputLabel":"Blank input @num of @total","inputHasTipLabel":"Tip available","tipLabel":"Tip","behaviour":{"enableRetry":true,"enableSolutionsButton":true,"enableCheckButton":true,"autoCheck":false,"caseSensitive":true,"showSolutionsRequiresInput":true,"separateLines":false,"disableImageZooming":false,"confirmCheckDialog":false,"confirmRetryDialog":false,"acceptSpellingErrors":false},"scoreBarLabel":"You got :num out of :total points","confirmCheck":{"header":"Finish ?","body":"Are you sure you wish to finish ?","cancelLabel":"Cancel","confirmLabel":"Finish"},"confirmRetry":{"header":"Retry ?","body":"Are you sure you wish to retry ?","cancelLabel":"Cancel","confirmLabel":"Confirm"},"questions":["<p>Not all *superheros*&nbsp;wear capes!<\/p>\n"]}';
        $h5p = H5PContent::factory()->create([
            'parameters' => $params,
            'library_id' => 284,
        ]);

        config(['feature.export_h5p_with_local_files' => false]);
        $export = resolve(H5PExport::class);
        $this->assertTrue($export->generateExport($h5p));
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
     * @throws Exception
     */
    #[Test]
    public function externalProviderFailureAbortsExportAndIdentifiesFile()
    {
        $this->setupH5PAdapter([
            'alterLibrarySemantics' => null,
        ]);

        $failingProvider = new class implements H5PImageInterface, H5PExternalProviderInterface {
            public function alterImageProperties($imageProperties, H5PAlterParametersSettingsDataObject $settings): object
            {
                return $imageProperties;
            }

            public function isTargetType($mimeType, $pathToFile): bool
            {
                return str_starts_with((string) $mimeType, 'image/');
            }

            public function storeContent($source, $content)
            {
                throw new Exception("The requested image 'undefined' was not found.");
            }

            public function getType(): string
            {
                return 'image';
            }

            public function getViewCss(): array
            {
                return [];
            }

            public function getViewScripts(): array
            {
                return [];
            }

            public function getEditorCss(): array
            {
                return [];
            }

            public function getEditorScripts(): array
            {
                return [];
            }

            public function getConfigJs(): array
            {
                return [];
            }
        };
        app()->instance(H5PImageInterface::class, $failingProvider);

        $this->seed(TestH5PSeeder::class);
        $params = '{"media":{"params":{"contentName":"Image","file":{"path":"https:\/\/api.ndla.no\/image-api\/raw\/undefined","mime":"image\/jpeg","copyright":{"license":"U"},"width":196,"height":358}},"library":"H5P.Image 1.1","metadata":{"contentType":"Image","license":"U","title":"Untitled Image","authors":[],"changes":[],"extraTitle":"Untitled Image"},"subContentId":"ca86c100-d25c-4e19-ac6b-f50f843da292"},"text":"Fill in the missing words","overallFeedback":[{"from":0,"to":100}],"showSolutions":"Show solution","tryAgain":"Retry","checkAnswer":"Check","notFilledOut":"Please fill in all blanks to view solution","answerIsCorrect":"&#039;:ans&#039; is correct","answerIsWrong":"&#039;:ans&#039; is wrong","answeredCorrectly":"Answered correctly","answeredIncorrectly":"Answered incorrectly","solutionLabel":"Correct answer:","inputLabel":"Blank input @num of @total","inputHasTipLabel":"Tip available","tipLabel":"Tip","behaviour":{"enableRetry":true,"enableSolutionsButton":true,"enableCheckButton":true,"autoCheck":false,"caseSensitive":true,"showSolutionsRequiresInput":true,"separateLines":false,"disableImageZooming":false,"confirmCheckDialog":false,"confirmRetryDialog":false,"acceptSpellingErrors":false},"scoreBarLabel":"You got :num out of :total points","confirmCheck":{"header":"Finish ?","body":"Are you sure you wish to finish ?","cancelLabel":"Cancel","confirmLabel":"Finish"},"confirmRetry":{"header":"Retry ?","body":"Are you sure you wish to retry ?","cancelLabel":"Cancel","confirmLabel":"Confirm"},"questions":["<p>Not all *superheros*&nbsp;wear capes!<\/p>\n"]}';
        $h5p = H5PContent::factory()->create([
            'parameters' => $params,
            'library_id' => 284,
        ]);

        config(['feature.export_h5p_with_local_files' => true]);
        $export = resolve(H5PExport::class);

        try {
            $export->generateExport($h5p);
            $this->fail('Expected H5PExternalMediaException to be thrown.');
        } catch (H5PExternalMediaException $exception) {
            $this->assertEquals('https://api.ndla.no/image-api/raw/undefined', $exception->getPath());
        }

        $exportName = sprintf("%s-%s.", $h5p->slug, $h5p->id);
        $exportPath = sprintf(ContentStorageSettings::EXPORT_PATH, $exportName . "h5p");
        $this->assertFileDoesNotExist($this->exportDisk->path($exportPath));
    }
}
