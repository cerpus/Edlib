<?php

namespace Tests\Integration\Libraries\H5P;

use App\Libraries\H5P\H5PCreateConfig;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class H5PCreateConfigTest extends TestCase
{
    use RefreshDatabase;

    public function test_getConfig(): void
    {
        $data = app(H5PCreateConfig::class)
            ->getConfig();

        // Check that the common attributes are present
        $this->assertObjectHasAttribute('baseUrl', $data);
        $this->assertObjectHasAttribute('url', $data);
        $this->assertObjectHasAttribute('postUserStatistics', $data);
        $this->assertObjectHasAttribute('ajaxPath', $data);
        $this->assertObjectHasAttribute('user', $data);
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
        $this->assertSame('en', $data->editor->language);
        $this->assertSame('en', $data->editor->defaultLanguage);
    }

    public function test_setDisplayHub(): void
    {
        $config = app(H5PCreateConfig::class);
        $config->setDisplayHub(true);
        $data = $config->getConfig();

        $this->assertTrue($data->hubIsEnabled);
    }
}
