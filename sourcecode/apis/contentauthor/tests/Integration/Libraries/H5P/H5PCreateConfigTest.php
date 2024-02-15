<?php

namespace Tests\Integration\Libraries\H5P;

use App\Libraries\H5P\H5PConfigAbstract;
use App\Libraries\H5P\H5PCreateConfig;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Session;
use Tests\TestCase;

class H5PCreateConfigTest extends TestCase
{
    use RefreshDatabase;

    /** @dataProvider provider_adapterMode */
    public function test_getConfig(string $adapterMode): void
    {
        config([
            'ndla-mode.h5p.audio.url' => 'https://ndla-audio.url',
            'ndla-mode.h5p.image.url' => 'https://ndla-image.url',
            "h5p.default-resource-language" => 'fi',
        ]);

        Session::put('adapterMode', $adapterMode);

        $config = app(H5PCreateConfig::class);
        $data = $config->getConfig();

        // Check that the common attributes are present
        $this->assertObjectHasAttribute('baseUrl', $data);
        $this->assertObjectHasAttribute('url', $data);
        $this->assertObjectHasAttribute('postUserStatistics', $data);
        $this->assertObjectHasAttribute('ajaxPath', $data);
        if (config('h5p.saveFrequency') === false) {
            $this->assertObjectNotHasAttribute('user', $data);
        } else {
            $this->assertObjectHasAttribute('user', $data);
        }
        $this->assertObjectHasAttribute('canGiveScore', $data);
        $this->assertObjectHasAttribute('hubIsEnabled', $data);
        $this->assertObjectHasAttribute('ajax', $data);
        $this->assertObjectHasAttribute('tokens', $data);
        $this->assertObjectHasAttribute('saveFreq', $data);
        $this->assertObjectHasAttribute('siteUrl', $data);
        $this->assertObjectHasAttribute('l10n', $data);
        $this->assertObjectHasAttribute('baseUrl', $data);
        $this->assertObjectHasAttribute('loadedJs', $data);
        $this->assertObjectHasAttribute('loadedCss', $data);
        $this->assertObjectHasAttribute('core', $data);
        $this->assertObjectHasAttribute('contents', $data);
        $this->assertObjectHasAttribute('crossorigin', $data);
        $this->assertObjectHasAttribute('crossoriginRegex', $data);
        $this->assertObjectHasAttribute('locale', $data);
        $this->assertObjectHasAttribute('localeConverted', $data);
        $this->assertObjectHasAttribute('pluginCacheBuster', $data);
        $this->assertObjectHasAttribute('libraryUrl', $data);

        // Attributes altered or set
        $this->assertSame('/api/progress?action=h5p_preview&c=1', $data->ajax['contentUserData']);
        $this->assertSame('/api/progress?action=h5p_preview&f=1', $data->ajax['setFinished']);

        $this->assertNotEmpty($data->editor->assets->css);
        $this->assertNotEmpty($data->editor->assets->js);
        $this->assertSame('/h5p-editor-php-library/', $data->editor->libraryUrl);
        $this->assertNotEmpty($data->editor->copyrightSemantics);
        $this->assertNotEmpty($data->editor->metadataSemantics);
        $this->assertStringStartsWith('/ajax?redirectToken=', $data->editor->ajaxPath);
        $this->assertStringEndsWith('&h5p_id=&action=', $data->editor->ajaxPath);
        $this->assertNotEmpty($data->editor->filesPath);
        $this->assertNotEmpty($data->editor->fileIcon['path']);
        $this->assertNotEmpty($data->editor->fileIcon['width']);
        $this->assertNotEmpty($data->editor->fileIcon['height']);
        $this->assertNotEmpty($data->editor->apiVersion['majorVersion']);
        $this->assertNotEmpty($data->editor->apiVersion['minorVersion']);
        $this->assertNotEmpty($data->editor->extraAllowedContent);
        $this->assertSame('fi', $data->editor->language);
        $this->assertSame('fi', $data->editor->defaultLanguage);

        // Adapter specific
        if ($adapterMode === 'ndla') {
            $this->assertContains('/css/ndlah5p-editor.css', $data->editor->assets->css);
            $this->assertContains('/js/cropperjs/cropper.min.css', $data->editor->assets->css);
            $this->assertContains('/css/ndlah5p-youtube.css', $data->editor->assets->css);

            $this->assertContains('/js/h5p/wiris/h5peditor-html-wiris-addon.js?ver=' . H5PConfigAbstract::CACHE_BUSTER_STRING, $data->editor->assets->js);
            $this->assertContains('/js/ndla-contentbrowser.js?ver=' . H5PConfigAbstract::CACHE_BUSTER_STRING, $data->editor->assets->js);
            $this->assertContains('/js/videos/brightcove.js?ver=' . H5PConfigAbstract::CACHE_BUSTER_STRING, $data->editor->assets->js);
            $this->assertContains('/js/h5peditor-image-popup.js?ver=' . H5PConfigAbstract::CACHE_BUSTER_STRING, $data->editor->assets->js);
            $this->assertContains('/js/h5peditor-custom.js?ver=' . H5PConfigAbstract::CACHE_BUSTER_STRING, $data->editor->assets->js);
            $this->assertContains('/js/h5p/ndlah5p-youtube.js?ver=' . H5PConfigAbstract::CACHE_BUSTER_STRING, $data->editor->assets->js);

            $this->assertSame('https://www.wiris.net/client/plugins/ckeditor/plugin.js', $data->editor->wirisPath);
            $this->assertSame('https://ndla-audio.url/audio-api/v1/audio', $data->audioBrowserDetailsUrl);
            $this->assertSame('https://ndla-image.url/image-api/v3/images', $data->imageBrowserDetailsUrl);
        } elseif ($adapterMode === 'cerpus') {
            $this->assertContains('/js/videos/streamps.js?ver=' . H5PConfigAbstract::CACHE_BUSTER_STRING, $data->editor->assets->js);
            $this->assertContains('/js/videos/brightcove.js?ver=' . H5PConfigAbstract::CACHE_BUSTER_STRING, $data->editor->assets->js);
            $this->assertObjectNotHasAttribute('wirisPath', $data->editor);
            $this->assertObjectNotHasAttribute('audioBrowserDetailsUrl', $data);
            $this->assertObjectNotHasAttribute('imageBrowserDetailsUrl', $data);
        }
    }

    public function provider_adapterMode(): \Generator
    {
        yield 'cerpusMode' => ['cerpus'];
        yield 'ndlaMode' => ['ndla'];
    }

    public function test_setDisplayHub(): void
    {
        $config = app(H5PCreateConfig::class);
        $config->setDisplayHub(true);
        $data = $config->getConfig();

        $this->assertTrue($data->hubIsEnabled);
    }
}
