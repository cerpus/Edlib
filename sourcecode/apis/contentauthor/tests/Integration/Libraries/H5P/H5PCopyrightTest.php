<?php

namespace Tests\Integration\Libraries\H5P;

use App\H5PContent;
use App\H5PContentsMetadata;
use App\H5PLibrary;
use App\Libraries\H5P\Dataobjects\H5PCopyrightAuthorDataObject;
use App\Libraries\H5P\Dataobjects\H5PCopyrightDataObject;
use App\Libraries\H5P\H5PCopyright;
use H5PCore;
use Illuminate\Foundation\Testing\RefreshDatabase;
use JsonException;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class H5PCopyrightTest extends TestCase
{
    use RefreshDatabase;

    public function testThrowsJsonExceptionOnInvalidData()
    {
        $h5pContent = H5PContent::factory()
            ->has(H5PLibrary::factory(), 'library')
            ->create(['filtered' => 'invalid json']);
        $h5pCopyright = new H5PCopyright(app(H5PCore::class));

        $this->expectException(JsonException::class);

        $h5pCopyright->getCopyrights($h5pContent);
    }

    #[Test]
    public function noImage_useCopyright()
    {
        $h5pContent = H5PContent::factory()->create([
            'filtered' => '{"data": [{"text":"Question 1","answer":"Answer 1","image":{"path":"","mime":"","copyright":{"license":"U"}}},{"text":"Question 2","answer":"Answer 2"}]}',
        ]);
        $h5pCopyright = new H5PCopyright(resolve(H5PCore::class));
        $this->assertEquals([
            'h5p' => null,
            'h5pLibrary' => null,
            'assets' => [],
        ], $h5pCopyright->getCopyrights($h5pContent));
    }

    #[Test]
    public function noImage_useMetadata()
    {
        $h5pContent = H5PContent::factory()->create([
            'filtered' => '{"data": [{"text":"Question 1","answer":"Answer 1","image":{"path":"","mime":"","metadata":{"license":"U"}}},{"text":"Question 2","answer":"Answer 2"}]}',
        ]);
        $h5pCopyright = new H5PCopyright(resolve(H5PCore::class));
        $this->assertEquals([
            'h5p' => null,
            'h5pLibrary' => null,
            'assets' => [],
        ], $h5pCopyright->getCopyrights($h5pContent));
    }

    #[Test]
    public function imageNoLicense_useCopyright()
    {
        $h5pContent = H5PContent::factory()->create([
            'filtered' => '{"data":[{"text":"Question 1","answer":"Answer 1","image":{"path":"images\/image-5c6e5e7364254.jpg","mime":"image\/jpeg","copyright":{"license":"U"}}},{"text":"Question 2","answer":"Answer 2"}]}',
        ]);
        $h5pCopyright = new H5PCopyright(resolve(H5PCore::class));
        $this->assertEquals([
            'h5p' => null,
            'h5pLibrary' => null,
            'assets' => [],
        ], $h5pCopyright->getCopyrights($h5pContent));
    }

    #[Test]
    public function imageNoLicense_useMetadata()
    {
        $h5pContent = H5PContent::factory()->create([
            'filtered' => '{"media":{"params":{"contentName":"Image","file":{"path":"images\/file-5c74f3b33fa76.jpg","mime":"image\/jpeg","copyright":{"license":"U"},"width":640,"height":640}},"library":"H5P.Image 1.1","metadata":{"contentType":"Image","license":"U","title":"Untitled Image"},"subContentId":"0e3aa323-15a7-41c4-8d5a-c1995646d349"}}',
        ]);
        $h5pCopyright = new H5PCopyright(resolve(H5PCore::class));
        $this->assertEquals([
            'h5p' => null,
            'h5pLibrary' => null,
            'assets' => [],
        ], $h5pCopyright->getCopyrights($h5pContent));
    }

    #[Test]
    public function imageWithLicense_useCopyright()
    {
        $h5pContent = H5PContent::factory()->create([
            'filtered' => '{"data":[{"text":"Question 1","answer":"Answer 1","image":{"path":"images\/image-5c6e5e7364254.jpg","mime":"image\/jpeg","copyright": {"license": "GNU GPL","title": "Elizabeth","author": "Me","version": "v3"},"width":3250,"height":4333}},{"text":"Question 2","answer":"Answer 2"}],"progressText":"Card @card of @total","next":"Next","previous":"Previous","checkAnswerText":"Check","showSolutionsRequiresInput":true}',
        ]);
        $h5pCopyright = new H5PCopyright(resolve(H5PCore::class));
        $expectedCopyright = H5PCopyrightDataObject::create([
            'title' => "Elizabeth",
            'license' => "GNU GPL",
            'licenseVersion' => 'v3',
            'authors' => [
                [
                    'name' => 'Me',
                    'role' => null,
                ],
            ],
            'contentType' => "Image",
            'thumbnail' => 'http://localhost/content/assets/content/' . $h5pContent->id . '/images/image-5c6e5e7364254.jpg',
        ]);
        $this->assertEquals([
            'h5p' => null,
            'h5pLibrary' => null,
            'assets' => [$expectedCopyright->toArray()],
        ], $h5pCopyright->getCopyrights($h5pContent));
    }


    #[Test]
    public function imageWithLicense_useMetadata()
    {
        $h5pContent = H5PContent::factory()->create([
            'filtered' => '{"elements":[{"dropZones":["0"],"type":{"library":"H5P.Image 1.1","params":{"contentName":"Image","file":{"path":"images\/backgroundimage.jpg","mime":"image\/jpeg","copyright":{"license":"U"},"width":3245,"height":3877},"alt":"Nagasagi"},"subContentId":"c3096441-8fa1-4c70-88de-7ae37568d12c","metadata":{"contentType":"Image","license":"CC BY","title":"Bomb","authors":[{"name":"Shakespeare","role":"Author"}],"licenseVersion":"4.0"}},"backgroundOpacity":100,"multiple":false}]}',
        ]);
        $h5pCopyright = new H5PCopyright(resolve(H5PCore::class));
        $expectedImageCopyright = H5PCopyrightDataObject::create([
            'license' => "CC BY",
            'title' => "Bomb",
            'licenseVersion' => '4.0',
            'authors' => [
                [
                    'name' => 'Shakespeare',
                    'role' => "Author",
                ],
            ],
            'thumbnail' => 'http://localhost/content/assets/content/' . $h5pContent->id . '/images/backgroundimage.jpg',
            'contentType' => 'Image',
        ])->toArray();


        $this->assertEquals([
            'h5p' => null,
            'h5pLibrary' => null,
            'assets' => [$expectedImageCopyright],
        ], $h5pCopyright->getCopyrights($h5pContent));
    }

    #[Test]
    public function imageAndText_withLicense_useCopyrightAndMetadata()
    {
        $h5pContent = H5PContent::factory()->create([
            'filtered' => '{"question":{"settings":{"size":{"width":620,"height":310},"background":{"path":"images\/einstein.jpg","mime":"image\/jpeg","copyright":{"license":"C","source":"http:\/\/stoneage.old"},"width":3250,"height":4333}},"task":{"elements":[{"x":6.4516129032258,"y":9.6774193548387,"width":7.125,"height":8.875,"dropZones":[],"type":{"library":"H5P.AdvancedText 1.1","params":{"text":"<p>A fool thinks himself to be wise, but a wise man knows himself to be a fool.<\/p>\n"},"subContentId":"b3d45ff8-1681-4537-946f-b8246ef2413b","metadata":{"contentType":"Text","license":"CC BY-SA","title":"Shakespeare quotes","authors":[{"name":"William Shakespeare","role":"Author"}],"licenseVersion":"4.0"}},"backgroundOpacity":100,"multiple":false},{"x":83.870967741935,"y":58.064516129032,"width":3.3557046979866,"height":5,"dropZones":["0"],"type":{"library":"H5P.Image 1.1","params":{"contentName":"Image","alt":"Mona Lisa","file":{"path":"images\/monalisa.jpg","mime":"image\/jpeg","copyright":{"license":"U"},"width":800,"height":1192}},"subContentId":"c3096441-8fa1-4c70-88de-7ae37568d12c","metadata":{"contentType":"Image","license":"CC BY","title":"Mona Lisa","authors":[{"name":"Leonardo Da Vinci","role":"Author"}],"licenseVersion":"4.0"}},"backgroundOpacity":100,"multiple":false}]}}}',
        ]);
        $h5pCopyright = new H5PCopyright(resolve(H5PCore::class));
        $expectedBackgroundImageCopyright = H5PCopyrightDataObject::create([
            'license' => "C",
            'source' => 'http://stoneage.old',
            'contentType' => 'Image',
            'thumbnail' => 'http://localhost/content/assets/content/' . $h5pContent->id . '/images/einstein.jpg',
        ])->toArray();
        $expectedTextCopyright = H5PCopyrightDataObject::create([
            'license' => "CC BY-SA",
            'licenseVersion' => '4.0',
            'title' => 'Shakespeare quotes',
            'authors' => [
                [
                    'name' => 'William Shakespeare',
                    'role' => "Author",
                ],
            ],
            'contentType' => 'Text',
        ])->toArray();
        $expectedImageCopyright = H5PCopyrightDataObject::create([
            'license' => "CC BY",
            'title' => "Mona Lisa",
            'licenseVersion' => '4.0',
            'authors' => [
                [
                    'name' => 'Leonardo Da Vinci',
                    'role' => "Author",
                ],
            ],
            'thumbnail' => 'http://localhost/content/assets/content/' . $h5pContent->id . '/images/monalisa.jpg',
            'contentType' => 'Image',
        ])->toArray();


        $this->assertEquals([
            'h5p' => null,
            'h5pLibrary' => null,
            'assets' => [$expectedBackgroundImageCopyright, $expectedTextCopyright, $expectedImageCopyright],
        ], $h5pCopyright->getCopyrights($h5pContent));
    }

    #[Test]
    public function video_useCopyright()
    {
        $h5pContent = H5PContent::factory()->create([
            'filtered' => '{"media":{"params":{"visuals":{"fit":true,"controls":true},"playback":{"autoplay":false,"loop":false},"sources":[{"path":"videos\/sources-5c74f64fc15d0.mp4","mime":"video\/mp4","copyright":{"license":"CC BY","version":"4.0","title":"Demo"}}]},"library":"H5P.Video 1.3","metadata":{"contentType":"Video","license":"U"},"subContentId":"05c0ef32-fb91-41c7-b4b1-7674c61795e4"}}',
        ]);
        $h5pCopyright = new H5PCopyright(resolve(H5PCore::class));
        $expectedVideoCopyright = H5PCopyrightDataObject::create([
            'license' => "CC BY",
            'licenseVersion' => "4.0",
            'title' => 'Demo',
            'contentType' => 'Video',
        ])->toArray();

        $this->assertEquals([
            'h5p' => null,
            'h5pLibrary' => null,
            'assets' => [$expectedVideoCopyright],
        ], $h5pCopyright->getCopyrights($h5pContent));
    }


    #[Test]
    public function video_withTitleAuthorAndLicense_useCopyright()
    {
        $h5pContent = H5PContent::factory()->create([
            'filtered' => '{"video":{"title":"Interactive Video","startScreenOptions":{"hideStartTitle":false},"files":[{"path":"videos\/files-5c73d5ef21a3c.mp4","mime":"video\/mp4","copyright":{"license":"PD","title":"Video","author":"Quinton Tarantino"}}],"copyright":""}}',
        ]);
        $h5pCopyright = new H5PCopyright(resolve(H5PCore::class));
        $expectedVideoCopyright = H5PCopyrightDataObject::create([
            'license' => "PD",
            'title' => 'Video',
            'contentType' => 'Video',
            'authors' => [
                H5PCopyrightAuthorDataObject::create([
                    'name' => 'Quinton Tarantino',
                ])->toArray(),
            ],
        ])->toArray();

        $this->assertEquals([
            'h5p' => null,
            'h5pLibrary' => null,
            'assets' => [$expectedVideoCopyright],
        ], $h5pCopyright->getCopyrights($h5pContent));
    }

    #[Test]
    public function video_withAllInfoSet_useCopyrights()
    {
        $h5pContent = H5PContent::factory()->create([
            'filtered' => '{"video":{"title":"Interactive Video","startScreenOptions":{"hideStartTitle":false},"copyright":"","files":[{"path":"https:\/\/www.youtube.com\/watch?v=F0jr-HQeT74","mime":"video\/YouTube","copyright":{"license":"PD","title":"Video","author":"Quinton Tarantino","year":"1900-2000","source":"https:\/\/www.youtube.com\/watch?v=F0jr-HQeT74","version":"CC PDM"}}]}}',
        ]);
        $h5pCopyright = new H5PCopyright(resolve(H5PCore::class));
        $expectedVideoCopyright = H5PCopyrightDataObject::create([
            'license' => "PD",
            'licenseVersion' => "CC PDM",
            'title' => 'Video',
            'contentType' => 'Video',
            'authors' => [
                H5PCopyrightAuthorDataObject::create([
                    'name' => 'Quinton Tarantino',
                ])->toArray(),
            ],
            'yearFrom' => '1900',
            'yearTo' => '2000',
            'source' => 'https://www.youtube.com/watch?v=F0jr-HQeT74',
        ])->toArray();

        $this->assertEquals([
            'h5p' => null,
            'h5pLibrary' => null,
            'assets' => [$expectedVideoCopyright],
        ], $h5pCopyright->getCopyrights($h5pContent));
    }

    #[Test]
    public function video_withAllInfoSet_useMetadata()
    {
        $h5pContent = H5PContent::factory()->create([
            'filtered' => '{"media":{"params":{"sources":[{"path":"videos\/sources-5c750cb31e0ca.mp4","mime":"video\/mp4","copyright":{"license":"U"}}]},"library":"H5P.Video 1.5","metadata":{"contentType":"Video","license":"CC BY","title":"Demo video","authors":[{"name":"Ola Nordmann","role":"Author"}],"licenseVersion":"3.0","yearFrom":2000,"yearTo":2010,"source":"https:\/\/mysecretsource.hidden","licenseExtras":"No info"},"subContentId":"99e08ff8-f4b8-468e-b069-55490ebfe1b7"}}',
        ]);
        $h5pCopyright = new H5PCopyright(resolve(H5PCore::class));
        $expectedVideoCopyright = H5PCopyrightDataObject::create([
            'license' => "CC BY",
            'licenseVersion' => "3.0",
            'title' => 'Demo video',
            'contentType' => 'Video',
            'authors' => [
                H5PCopyrightAuthorDataObject::create([
                    'name' => 'Ola Nordmann',
                    'role' => 'Author',
                ])->toArray(),
            ],
            'yearFrom' => 2000,
            'yearTo' => 2010,
            'source' => 'https://mysecretsource.hidden',
        ])->toArray();

        $this->assertEquals([
            'h5p' => null,
            'h5pLibrary' => null,
            'assets' => [$expectedVideoCopyright],
        ], $h5pCopyright->getCopyrights($h5pContent));
    }

    #[Test]
    public function noMedia_withContentMetadata()
    {
        $h5pContentMetadata = H5PContentsMetadata::factory()->create([
            'license' => "CC BY",
        ]);
        $library = H5PLibrary::factory()->create([
            'semantics' => '[{"name":"interactiveVideo","type":"group","widget":"wizard","label":"Interactive Video Editor","importance":"high","fields":[{"name":"video","type":"group","label":"Upload/embed video","importance":"high","fields":[{"name":"files","type":"video","label":"Add a video","importance":"high","description":"Click below to add a video you wish to use in your interactive video. You can add a video link or upload video files. It is possible to add several versions of the video with different qualities. To ensure maximum support in browsers at least add a version in webm and mp4 formats.","extraAttributes":["metadata"],"enableCustomQualityLabel":true},{"name":"startScreenOptions","type":"group","label":"Start screen options (unsupported for YouTube videos)","importance":"low","fields":[{"name":"title","type":"text","label":"The title of this interactive video","importance":"low","maxLength":60,"default":"Interactive Video","description":"Used in summaries, statistics etc."},{"name":"hideStartTitle","type":"boolean","label":"Hide title on video start screen","importance":"low","optional":true,"default":false},{"name":"shortStartDescription","type":"text","label":"Short description (Optional)","importance":"low","optional":true,"maxLength":120,"description":"Optional. Display a short description text on the video start screen. Does not work for YouTube videos."},{"name":"poster","type":"image","label":"Poster image","importance":"low","optional":true,"description":"Image displayed before the user launches the video. Does not work for YouTube Videos."}]},{"name":"textTracks","type":"group","label":"Text tracks (unsupported for YouTube videos)","importance":"low","fields":[{"name":"videoTrack","type":"list","label":"Available text tracks","importance":"low","optional":true,"entity":"Track","min":0,"defaultNum":1,"field":{"name":"track","type":"group","label":"Track","importance":"low","expanded":false,"fields":[{"name":"label","type":"text","label":"Track label","description":"Used if you offer multiple tracks and the user has to choose a track. For instance \'Spanish subtitles\' could be the label of a Spanish subtitle track.","importance":"low","default":"Subtitles","optional":true},{"name":"kind","type":"select","label":"Type of text track","importance":"low","default":"subtitles","options":[{"value":"subtitles","label":"Subtitles"},{"value":"captions","label":"Captions"},{"value":"descriptions","label":"Descriptions"}]},{"name":"srcLang","type":"text","label":"Source language, must be defined for subtitles","importance":"low","default":"en","description":"Must be a valid BCP 47 language tag. If \'Subtitles\' is the type of text track selected, the source language of the track must be defined."},{"name":"track","type":"file","label":"Track source (WebVTT file)","importance":"low"}]}}]}]},{"name":"assets","type":"group","label":"Add interactions","importance":"high","widget":"interactiveVideo","video":"video/files","poster":"video/startScreenOptions/poster","fields":[{"name":"interactions","type":"list","field":{"name":"interaction","type":"group","fields":[{"name":"duration","type":"group","widget":"duration","label":"Display time","importance":"low","fields":[{"name":"from","type":"number"},{"name":"to","type":"number"}]},{"name":"pause","label":"Pause video","importance":"low","type":"boolean"},{"name":"displayType","label":"Display as","importance":"low","description":"<b>Button</b> is a collapsed interaction the user must press to open. <b>Poster</b> is an expanded interaction displayed directly on top of the video","type":"select","widget":"imageRadioButtonGroup","options":[{"value":"button","label":"Button"},{"value":"poster","label":"Poster"}],"default":"button"},{"name":"buttonOnMobile","label":"Turn into button on small screens","importance":"low","type":"boolean","default":false},{"name":"label","type":"text","widget":"html","label":"Label","importance":"low","description":"Label displayed next to interaction icon.","optional":true,"enterMode":"p","tags":["p"]},{"name":"x","type":"number","importance":"low","widget":"none"},{"name":"y","type":"number","importance":"low","widget":"none"},{"name":"width","type":"number","widget":"none","importance":"low","optional":true},{"name":"height","type":"number","widget":"none","importance":"low","optional":true},{"name":"libraryTitle","type":"text","importance":"low","optional":true,"widget":"none"},{"name":"action","type":"library","importance":"low","options":["H5P.Nil 1.0","H5P.Text 1.1","H5P.Table 1.1","H5P.Link 1.3","H5P.Image 1.1","H5P.Summary 1.10","H5P.SingleChoiceSet 1.11","H5P.MultiChoice 1.13","H5P.TrueFalse 1.5","H5P.Blanks 1.11","H5P.DragQuestion 1.13","H5P.MarkTheWords 1.9","H5P.DragText 1.8","H5P.GoToQuestion 1.3","H5P.IVHotspot 1.2","H5P.Questionnaire 1.2","H5P.FreeTextQuestion 1.0"]},{"name":"adaptivity","type":"group","label":"Adaptivity","importance":"low","optional":true,"fields":[{"name":"correct","type":"group","label":"Action on all correct","fields":[{"name":"seekTo","type":"number","widget":"timecode","label":"Seek to","description":"Enter timecode in the format M:SS"},{"name":"allowOptOut","type":"boolean","label":"Allow the user to opt out and continue"},{"name":"message","type":"text","widget":"html","enterMode":"p","tags":["strong","em","del","a"],"label":"Message"},{"name":"seekLabel","type":"text","label":"Label for seek button"}]},{"name":"wrong","type":"group","label":"Action on wrong","fields":[{"name":"seekTo","type":"number","widget":"timecode","label":"Seek to","description":"Enter timecode in the format M:SS"},{"name":"allowOptOut","type":"boolean","label":"Allow the user to opt out and continue"},{"name":"message","type":"text","widget":"html","enterMode":"p","tags":["strong","em","del","a"],"label":"Message"},{"name":"seekLabel","type":"text","label":"Label for seek button"}]},{"name":"requireCompletion","type":"boolean","label":"Require full score for task before proceeding","description":"For best functionality this option should be used in conjunction with the \"Prevent skipping forward in a video\" option of Interactive Video."}]},{"name":"visuals","label":"Visuals","importance":"low","type":"group","fields":[{"name":"backgroundColor","type":"text","label":"Background color","widget":"colorSelector","default":"rgb(255, 255, 255)","spectrum":{"showInput":true,"showAlpha":true,"preferredFormat":"rgb","showPalette":true,"palette":[["rgba(0, 0, 0, 0)"],["rgb(67, 67, 67)","rgb(102, 102, 102)","rgb(204, 204, 204)","rgb(217, 217, 217)","rgb(255, 255, 255)"],["rgb(152, 0, 0)","rgb(255, 0, 0)","rgb(255, 153, 0)","rgb(255, 255, 0)","rgb(0, 255, 0)","rgb(0, 255, 255)","rgb(74, 134, 232)","rgb(0, 0, 255)","rgb(153, 0, 255)","rgb(255, 0, 255)"],["rgb(230, 184, 175)","rgb(244, 204, 204)","rgb(252, 229, 205)","rgb(255, 242, 204)","rgb(217, 234, 211)","rgb(208, 224, 227)","rgb(201, 218, 248)","rgb(207, 226, 243)","rgb(217, 210, 233)","rgb(234, 209, 220)","rgb(221, 126, 107)","rgb(234, 153, 153)","rgb(249, 203, 156)","rgb(255, 229, 153)","rgb(182, 215, 168)","rgb(162, 196, 201)","rgb(164, 194, 244)","rgb(159, 197, 232)","rgb(180, 167, 214)","rgb(213, 166, 189)","rgb(204, 65, 37)","rgb(224, 102, 102)","rgb(246, 178, 107)","rgb(255, 217, 102)","rgb(147, 196, 125)","rgb(118, 165, 175)","rgb(109, 158, 235)","rgb(111, 168, 220)","rgb(142, 124, 195)","rgb(194, 123, 160)","rgb(166, 28, 0)","rgb(204, 0, 0)","rgb(230, 145, 56)","rgb(241, 194, 50)","rgb(106, 168, 79)","rgb(69, 129, 142)","rgb(60, 120, 216)","rgb(61, 133, 198)","rgb(103, 78, 167)","rgb(166, 77, 121)","rgb(91, 15, 0)","rgb(102, 0, 0)","rgb(120, 63, 4)","rgb(127, 96, 0)","rgb(39, 78, 19)","rgb(12, 52, 61)","rgb(28, 69, 135)","rgb(7, 55, 99)","rgb(32, 18, 77)","rgb(76, 17, 48)"]]}},{"name":"boxShadow","type":"boolean","label":"Box shadow","default":true,"description":"Adds a subtle shadow around the interaction. You might want to disable this for completely transparent interactions"}]},{"name":"goto","label":"Go to on click","importance":"low","type":"group","fields":[{"name":"type","label":"Type","type":"select","widget":"selectToggleFields","options":[{"value":"timecode","label":"Timecode","hideFields":["url"]},{"value":"url","label":"Another page (URL)","hideFields":["time"]}],"optional":true},{"name":"time","type":"number","widget":"timecode","label":"Go To","description":"The target time the user will be taken to upon pressing the hotspot. Enter timecode in the format M:SS.","optional":true},{"name":"url","type":"group","label":"URL","widget":"linkWidget","optional":true,"fields":[{"name":"protocol","type":"select","label":"Protocol","options":[{"value":"http://","label":"http://"},{"value":"https://","label":"https://"},{"value":"/","label":"(root relative)"},{"value":"other","label":"other"}],"optional":true,"default":"http://"},{"name":"url","type":"text","label":"URL","optional":true}]},{"name":"visualize","type":"boolean","label":"Visualize","description":"Show that interaction can be clicked by adding a border and an icon"}]}]}},{"name":"bookmarks","importance":"low","type":"list","field":{"name":"bookmark","type":"group","fields":[{"name":"time","type":"number"},{"name":"label","type":"text"}]}},{"name":"endscreens","importance":"low","type":"list","field":{"name":"endscreen","type":"group","fields":[{"name":"time","type":"number"},{"name":"label","type":"text"}]}}]},{"name":"summary","type":"group","label":"Summary task","importance":"high","fields":[{"name":"task","type":"library","options":["H5P.Summary 1.10"],"default":{"library":"H5P.Summary 1.10","params":{}}},{"name":"displayAt","type":"number","label":"Display at","description":"Number of seconds before the video ends.","default":3}]}]},{"name":"override","type":"group","label":"Behavioural settings","importance":"low","optional":true,"fields":[{"name":"startVideoAt","type":"number","widget":"timecode","label":"Start video at","importance":"low","optional":true,"description":"Enter timecode in the format M:SS"},{"name":"autoplay","type":"boolean","label":"Auto-play video","default":false,"optional":true,"description":"Start playing the video automatically"},{"name":"loop","type":"boolean","label":"Loop the video","default":false,"optional":true,"description":"Check if video should run in a loop"},{"name":"showSolutionButton","type":"select","label":"Override \"Show Solution\" button","importance":"low","description":"This option determines if the \"Show Solution\" button will be shown for all questions, disabled for all or configured for each question individually.","optional":true,"options":[{"value":"on","label":"Enabled"},{"value":"off","label":"Disabled"}]},{"name":"retryButton","type":"select","label":"Override \"Retry\" button","importance":"low","description":"This option determines if the \"Retry\" button will be shown for all questions, disabled for all or configured for each question individually.","optional":true,"options":[{"value":"on","label":"Enabled"},{"value":"off","label":"Disabled"}]},{"name":"showBookmarksmenuOnLoad","type":"boolean","label":"Start with bookmarks menu open","importance":"low","default":false,"description":"This function is not available on iPad when using YouTube as video source."},{"name":"showRewind10","type":"boolean","label":"Show button for rewinding 10 seconds","importance":"low","default":false},{"name":"preventSkipping","type":"boolean","default":false,"label":"Prevent skipping forward in a video","importance":"low","description":"Enabling this options will disable user video navigation through default controls."},{"name":"deactivateSound","type":"boolean","default":false,"label":"Deactivate sound","importance":"low","description":"Enabling this option will deactivate the video\'s sound and prevent it from being switched on."}]},{"name":"l10n","type":"group","label":"Localize","importance":"low","common":true,"optional":true,"fields":[{"name":"interaction","type":"text","label":"Interaction title","importance":"low","default":"Interaction","optional":true},{"name":"play","type":"text","label":"Play title","importance":"low","default":"Play","optional":true},{"name":"pause","type":"text","label":"Pause title","importance":"low","default":"Pause","optional":true},{"name":"mute","type":"text","label":"Mute title","importance":"low","default":"Mute","optional":true},{"name":"unmute","type":"text","label":"Unmute title","importance":"low","default":"Unmute","optional":true},{"name":"quality","type":"text","label":"Video quality title","importance":"low","default":"Video Quality","optional":true},{"name":"captions","type":"text","label":"Video captions title","importance":"low","default":"Captions","optional":true},{"name":"close","type":"text","label":"Close button text","importance":"low","default":"Close","optional":true},{"name":"fullscreen","type":"text","label":"Fullscreen title","importance":"low","default":"Fullscreen","optional":true},{"name":"exitFullscreen","type":"text","label":"Exit fullscreen title","importance":"low","default":"Exit Fullscreen","optional":true},{"name":"summary","type":"text","label":"Summary title","importance":"low","default":"Summary","optional":true},{"name":"bookmarks","type":"text","label":"Bookmarks title","importance":"low","default":"Bookmarks","optional":true},{"name":"endscreen","type":"text","label":"Submit screen title","importance":"low","default":"Submit screen","optional":true},{"name":"defaultAdaptivitySeekLabel","type":"text","label":"Default label for adaptivity seek button","importance":"low","default":"Continue","optional":true},{"name":"continueWithVideo","type":"text","label":"Default label for continue video button","importance":"low","default":"Continue with video","optional":true},{"name":"playbackRate","type":"text","label":"Set playback rate","importance":"low","default":"Playback Rate","optional":true},{"name":"rewind10","type":"text","label":"Rewind 10 Seconds","importance":"low","default":"Rewind 10 Seconds","optional":true},{"name":"navDisabled","type":"text","label":"Navigation is disabled text","importance":"low","default":"Navigation is disabled","optional":true},{"name":"sndDisabled","type":"text","label":"Sound is disabled text","importance":"low","default":"Sound is disabled","optional":true},{"name":"requiresCompletionWarning","type":"text","label":"Warning that the user must answer the question correctly before continuing","importance":"low","default":"You need to answer all the questions correctly before continuing.","optional":true},{"name":"back","type":"text","label":"Back button","importance":"low","default":"Back","optional":true},{"name":"hours","type":"text","label":"Passed time hours","importance":"low","default":"Hours","optional":true},{"name":"minutes","type":"text","label":"Passed time minutes","importance":"low","default":"Minutes","optional":true},{"name":"seconds","type":"text","label":"Passed time seconds","importance":"low","default":"Seconds","optional":true},{"name":"currentTime","type":"text","label":"Label for current time","importance":"low","default":"Current time:","optional":true},{"name":"totalTime","type":"text","label":"Label for total time","importance":"low","default":"Total time:","optional":true},{"name":"navigationHotkeyInstructions","type":"text","label":"Text for explaining navigation hotkey","importance":"low","default":"Use key k for starting and stopping video at any time","optional":true},{"name":"singleInteractionAnnouncement","type":"text","label":"Text explaining that a single interaction with a name has come into view","importance":"low","default":"Interaction appeared:","optional":true},{"name":"multipleInteractionsAnnouncement","type":"text","label":"Text for explaining that multiple interactions have come into view","importance":"low","default":"Multiple interactions appeared.","optional":true},{"name":"videoPausedAnnouncement","type":"text","label":"Video is paused announcement","importance":"low","default":"Video is paused","optional":true},{"name":"content","type":"text","label":"Content label","importance":"low","default":"Content","optional":true},{"name":"answered","type":"text","label":"Answered message (@answered will be replaced with the number of answered questions)","importance":"low","default":"@answered answered","optional":true},{"name":"endcardTitle","type":"text","label":"Submit screen title","importance":"low","default":"@answered Question(s) answered","description":"@answered will be replaced by the number of answered questions.","optional":true},{"name":"endcardInformation","type":"text","label":"Submit screen information","importance":"low","default":"You have answered @answered questions, click below to submit your answers.","description":"@answered will be replaced by the number of answered questions.","optional":true},{"name":"endcardInformationNoAnswers","type":"text","label":"Submit screen information for missing answers","importance":"low","default":"You have not answered any questions.","optional":true},{"name":"endcardInformationMustHaveAnswer","type":"text","label":"Submit screen information for answer needed","importance":"low","default":"You have to answer at least one question before you can submit your answers.","optional":true},{"name":"endcardSubmitButton","type":"text","label":"Submit screen submit button","importance":"low","default":"Submit Answers","optional":true},{"name":"endcardSubmitMessage","type":"text","label":"Submit screen submit message","importance":"low","default":"Your answers have been submitted!","optional":true},{"name":"endcardTableRowAnswered","type":"text","label":"Submit screen table row title: Answered questions","importance":"low","default":"Answered questions","optional":true},{"name":"endcardTableRowScore","type":"text","label":"Submit screen table row title: Score","importance":"low","default":"Score","optional":true},{"name":"endcardAnsweredScore","type":"text","label":"Submit screen answered score","importance":"low","default":"answered","optional":true}]}]',
        ]);
        /** @var H5PContent $h5pContent */
        $h5pContent = $h5pContentMetadata->content()->first();
        $h5pContent->library()->associate($library);
        $h5pContent->parameters = '{"media":{"params":{"sources":[{"path":"videos\/sources-5c750cb31e0ca.mp4","mime":"video\/mp4","copyright":{"license":"U"}}]},"library":"H5P.Video 1.5","metadata":{"contentType":"Video","license":"CC BY","title":"Demo video","authors":[{"name":"Ola Nordmann","role":"Author"}],"licenseVersion":"3.0","yearFrom":2000,"yearTo":2010,"source":"https:\/\/mysecretsource.hidden","licenseExtras":"No info"},"subContentId":"99e08ff8-f4b8-468e-b069-55490ebfe1b7"}}';

        $h5pCopyright = new H5PCopyright(resolve(H5PCore::class));
        $expectedCopyright = H5PCopyrightDataObject::create([
            'title' => $h5pContent->title,
            'license' => "CC BY",
            'contentType' => 'H5P',
        ])->toArray();
        $actualCopyright = $h5pCopyright->getCopyrights($h5pContent);
        $this->assertEquals([
            'h5p' => $expectedCopyright,
            'h5pLibrary' => [
                'majorVersion' => $library->major_version,
                'minorVersion' => $library->minor_version,
                'name' => $library->name,
            ],
            'assets' => [],
        ], $actualCopyright);
    }

    #[Test]
    public function noMedia_withAuthorAsNull_useMetadata()
    {
        $h5pContentMetadata = H5PContentsMetadata::factory()->create([
            'license' => "CC BY",
            'authors' => null,
        ]);
        $library = H5PLibrary::factory()->create();
        /** @var H5PContent $h5pContent */
        $h5pContent = $h5pContentMetadata->content()->first();
        $h5pContent->library()->associate($library);
        $h5pContent->parameters = '{"media":{"params":{"sources":[{"path":"videos\/sources-5c750cb31e0ca.mp4","mime":"video\/mp4","copyright":{"license":"U"}}]},"library":"H5P.Video 1.5","metadata":{"contentType":"Video","license":"CC BY","title":"Demo video","authors":[{"name":"Ola Nordmann","role":"Author"}],"licenseVersion":"3.0","yearFrom":2000,"yearTo":2010,"source":"https:\/\/mysecretsource.hidden","licenseExtras":"No info"},"subContentId":"99e08ff8-f4b8-468e-b069-55490ebfe1b7"}}';

        $h5pCopyright = new H5PCopyright(resolve(H5PCore::class));
        $expectedCopyright = H5PCopyrightDataObject::create([
            'title' => $h5pContent->title,
            'license' => "CC BY",
            'authors' => null,
            'contentType' => 'H5P',
        ])->toArray();
        $actualCopyright = $h5pCopyright->getCopyrights($h5pContent);
        $this->assertEquals([
            'h5p' => $expectedCopyright,
            'h5pLibrary' => [
                'majorVersion' => $library->major_version,
                'minorVersion' => $library->minor_version,
                'name' => $library->name,
            ],
            'assets' => [],
        ], $actualCopyright);
    }

    #[Test]
    public function media_withContentMetadata()
    {
        $h5pContentMetadata = H5PContentsMetadata::factory()->create([
            'license' => "CC BY",
            'license_version' => "4.0",
        ]);
        /** @var H5PContent $h5pContent */
        $h5pContent = $h5pContentMetadata->content()->first();
        $h5pContent->filtered = '{"media":{"params":{"sources":[{"path":"videos\/sources-5c750cb31e0ca.mp4","mime":"video\/mp4","copyright":{"license":"U"}}]},"library":"H5P.Video 1.5","metadata":{"contentType":"Video","license":"CC BY","title":"Demo video","authors":[{"name":"Ola Nordmann","role":"Author"}],"licenseVersion":"3.0","yearFrom":2000,"yearTo":2010,"source":"https:\/\/mysecretsource.hidden","licenseExtras":"No info"},"subContentId":"99e08ff8-f4b8-468e-b069-55490ebfe1b7"}}';

        $h5pCopyright = new H5PCopyright(resolve(H5PCore::class));
        $expectedCopyright = H5PCopyrightDataObject::create([
            'title' => $h5pContent->title,
            'license' => "CC BY",
            'licenseVersion' => "4.0",
            'contentType' => 'H5P',
        ])->toArray();
        $expectedVideoCopyright = H5PCopyrightDataObject::create([
            'license' => "CC BY",
            'licenseVersion' => "3.0",
            'title' => 'Demo video',
            'contentType' => 'Video',
            'authors' => [
                H5PCopyrightAuthorDataObject::create([
                    'name' => 'Ola Nordmann',
                    'role' => 'Author',
                ])->toArray(),
            ],
            'yearFrom' => 2000,
            'yearTo' => 2010,
            'source' => 'https://mysecretsource.hidden',
        ])->toArray();

        $this->assertEquals([
            'h5p' => $expectedCopyright,
            'h5pLibrary' => null,
            'assets' => [$expectedVideoCopyright],
        ], $h5pCopyright->getCopyrights($h5pContent));
    }

    #[Test]
    public function noMedia_withLibrary()
    {
        $h5pLibrary = H5PLibrary::factory()->create([
            'name' => 'H5P.Hey',
            'major_version' => 1,
            'minor_version' => 20,
        ]);

        $h5pContent = H5PContent::factory()->create([
            'library_id' => $h5pLibrary->id,
            'filtered' => '[]',
        ]);

        $actualCopyright = (new H5PCopyright(resolve(H5PCore::class)))->getCopyrights($h5pContent);
        $this->assertEquals([
            'h5p' => null,
            'h5pLibrary' => [
                'name' => 'H5P.Hey',
                'majorVersion' => 1,
                'minorVersion' => 20,
            ],
            'assets' => [],
        ], $actualCopyright);
    }

    #[Test]
    public function cerpusImageWithLicense_useMetadata()
    {
        $h5pContentMetadata = H5PContentsMetadata::factory()->create([
            'license' => "CC BY",
            'license_version' => "4.0",
        ]);
        $h5pLibrary = H5PLibrary::factory()->create([
            'name' => "H5P.CerpusImage",
            'major_version' => 1,
            'minor_version' => 0,
        ]);
        /** @var H5PContent $h5pContent */
        $h5pContent = $h5pContentMetadata->content()->first();
        $h5pContent->library_id = $h5pLibrary->id;
        $h5pContent->filtered = '{"contentName":"Image","file":{"path":"images\/testimage.jpg","mime":"image\/jpeg","copyright":{"license":"U"},"width":1024,"height":615},"alt":"Alt text for image"}';

        $h5pCopyright = new H5PCopyright(resolve(H5PCore::class));
        $expectedCopyright = H5PCopyrightDataObject::create([
            'title' => $h5pContent->title,
            'license' => "CC BY",
            'licenseVersion' => "4.0",
            'contentType' => 'Image',
            'thumbnail' => sprintf('http://localhost/content/assets/content/%s/images/testimage.jpg', $h5pContent->id),
        ])->toArray();

        $this->assertEquals([
            'h5p' => $expectedCopyright,
            'h5pLibrary' => [
                'name' => 'H5P.CerpusImage',
                'majorVersion' => 1,
                'minorVersion' => 0,
            ],
            'assets' => [],
        ], $h5pCopyright->getCopyrights($h5pContent));
    }

    #[Test]
    public function cerpusVideoWithLicense_useMetadata()
    {
        $h5pContentMetadata = H5PContentsMetadata::factory()->create([
            'license' => "CC BY-ND",
            'license_version' => "4.0",
        ]);
        $h5pLibrary = H5PLibrary::factory()->create([
            'name' => "H5P.CerpusVideo",
            'major_version' => 1,
            'minor_version' => 0,
        ]);
        /** @var H5PContent $h5pContent */
        $h5pContent = $h5pContentMetadata->content()->first();
        $h5pContent->library_id = $h5pLibrary->id;
        $h5pContent->filtered = '{"visuals":{"fit":true,"controls":true},"playback":{"autoplay":false,"loop":false},"l10n":{"name":"Video","loading":"Video player loading...","noPlayers":"Found no video players that supports the given video format.","noSources":"Video is missing sources.","aborted":"Media playback has been aborted.","networkFailure":"Network failure.","cannotDecode":"Unable to decode media.","formatNotSupported":"Video format not supported.","mediaEncrypted":"Media encrypted.","unknownError":"Unknown error.","invalidYtId":"Invalid YouTube ID.","unknownYtId":"Unable to find video with the given YouTube ID.","restrictedYt":"The owner of this video does not allow it to be embedded."},"sources":[{"path":"https:\/\/www.youtube.com\/watch?v=66MJEpsfFEU","mime":"video\/YouTube","copyright":{"license":"U"}}]}';

        $h5pCopyright = new H5PCopyright(resolve(H5PCore::class));
        $expectedCopyright = H5PCopyrightDataObject::create([
            'title' => $h5pContent->title,
            'license' => "CC BY-ND",
            'licenseVersion' => "4.0",
            'contentType' => 'Video',
        ])->toArray();

        $this->assertEquals([
            'h5p' => $expectedCopyright,
            'h5pLibrary' => [
                'name' => 'H5P.CerpusVideo',
                'majorVersion' => 1,
                'minorVersion' => 0,
            ],
            'assets' => [],
        ], $h5pCopyright->getCopyrights($h5pContent));
    }
}
