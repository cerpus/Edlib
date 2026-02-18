<?php

namespace Tests\Integration\Libraries\H5P;

use App\H5PContent;
use App\H5PContentsMetadata;
use App\H5PLibrary;
use App\Libraries\H5P\H5PConfigAbstract;
use App\Libraries\H5P\H5PEditConfig;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Session;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class H5PEditConfigTest extends TestCase
{
    use RefreshDatabase;

    #[DataProvider('provider_adapterMode')]
    public function test_getConfig(string $adapterMode): void
    {
        Config::set('ndla-mode.h5p.audio.url', 'https://audio.url');
        Config::set('ndla-mode.h5p.image.url', 'https://ndla-image.url');

        Session::put('adapterMode', $adapterMode);

        $config = app(H5PEditConfig::class);
        $data = $config->getConfig();

        // Check that the common attributes are present
        $this->assertObjectHasProperty('baseUrl', $data);
        $this->assertObjectHasProperty('url', $data);
        $this->assertObjectHasProperty('postUserStatistics', $data);
        $this->assertObjectHasProperty('ajaxPath', $data);
        if (config('h5p.saveFrequency') === false) {
            $this->assertObjectNotHasProperty('user', $data);
        } else {
            $this->assertObjectHasProperty('user', $data);
        }
        $this->assertObjectHasProperty('canGiveScore', $data);
        $this->assertObjectHasProperty('hubIsEnabled', $data);
        $this->assertObjectHasProperty('ajax', $data);
        $this->assertObjectHasProperty('tokens', $data);
        $this->assertObjectHasProperty('saveFreq', $data);
        $this->assertObjectHasProperty('siteUrl', $data);
        $this->assertObjectHasProperty('l10n', $data);
        $this->assertObjectHasProperty('baseUrl', $data);
        $this->assertObjectHasProperty('loadedJs', $data);
        $this->assertObjectHasProperty('loadedCss', $data);
        $this->assertObjectHasProperty('core', $data);
        $this->assertObjectHasProperty('contents', $data);
        $this->assertObjectHasProperty('crossorigin', $data);
        $this->assertObjectHasProperty('crossoriginRegex', $data);
        $this->assertObjectHasProperty('locale', $data);
        $this->assertObjectHasProperty('localeConverted', $data);
        $this->assertObjectHasProperty('pluginCacheBuster', $data);
        $this->assertObjectHasProperty('libraryUrl', $data);

        // Attributes altered or set
        $this->assertSame('/api/progress?action=h5p_preview&c=1', $data->ajax['contentUserData']);
        $this->assertSame('/api/progress?action=h5p_preview&f=1', $data->ajax['setFinished']);
        $this->assertNotEmpty($data->editor->assets->css);
        $this->assertNotEmpty($data->editor->assets->js);
        $this->assertSame('/h5p-editor-php-library/', $data->editor->libraryUrl);
        $this->assertNotEmpty($data->editor->copyrightSemantics);
        $this->assertNotEmpty($data->editor->metadataSemantics);
        $this->assertEmpty($data->editor->ajaxPath);
        $this->assertNull($data->editor->nodeVersionId);
        $this->assertNotEmpty($data->editor->filesPath);
        $this->assertNotEmpty($data->editor->fileIcon['path']);
        $this->assertNotEmpty($data->editor->fileIcon['width']);
        $this->assertNotEmpty($data->editor->fileIcon['height']);
        $this->assertNotEmpty($data->editor->apiVersion['majorVersion']);
        $this->assertNotEmpty($data->editor->apiVersion['minorVersion']);
        $this->assertNotEmpty($data->editor->extraAllowedContent);
        $this->assertSame('en', $data->editor->language);
        $this->assertSame('en', $data->editor->defaultLanguage);

        // Adapter specific
        if ($adapterMode === 'ndla') {
            $this->assertContains('/js/cropperjs/cropper.min.css', $data->editor->assets->css);
            $this->assertContains('/js/ndla-h5peditor-html.js?ver=' . H5PConfigAbstract::CACHE_BUSTER_STRING, $data->editor->assets->js);
            $this->assertContains('/js/h5peditor-image-popup.js?ver=' . H5PConfigAbstract::CACHE_BUSTER_STRING, $data->editor->assets->js);
            $this->assertContains('/js/h5peditor-custom.js?ver=' . H5PConfigAbstract::CACHE_BUSTER_STRING, $data->editor->assets->js);
        } elseif ($adapterMode === 'cerpus') {
            $this->assertObjectNotHasProperty('wirisPath', $data->editor);
            $this->assertNotContains('/js/ndla-h5peditor-html.js?ver=' . H5PConfigAbstract::CACHE_BUSTER_STRING, $data->editor->assets->js);
        }
    }

    public static function provider_adapterMode(): \Generator
    {
        yield 'cerpus' => ['cerpus'];
        yield 'ndla' => ['ndla'];
    }

    public function test_loadContent(): void
    {
        $library = H5PLibrary::factory()->create();
        $content = H5PContent::factory()->create([
            'library_id' => $library->id,
            'is_draft' => false,
            'max_score' => 42,
            'language_iso_639_3' => 'nob',
            'license' => 'BY-NC-ND',
        ]);
        H5PContentsMetadata::factory()->create([
            'content_id' => $content->id,
            'default_language' => 'nb',
        ]);
        $config = app(H5PEditConfig::class)
            ->loadContent($content->id)
            ->getConfig();

        $this->assertSame($content->id, $config->editor->nodeVersionId);
        $this->assertTrue($config->canGiveScore);

        $this->assertSame('nb', $config->editor->language);
        $this->assertStringContainsString(sprintf("&h5p_id=%d&", $content->id), $config->editor->ajaxPath);
    }
}
