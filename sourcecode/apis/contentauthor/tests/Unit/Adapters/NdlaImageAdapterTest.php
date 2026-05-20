<?php

namespace Tests\Unit\Adapters;


use App\Libraries\H5P\Dataobjects\H5PAlterParametersSettingsDataObject;
use App\Libraries\H5P\Image\NdlaImageAdapter;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Tests\TestCase;

class NdlaImageAdapterTest extends TestCase
{

    public function setUp(): void
    {
        parent::setUp();

        Config::set([
            'ndla' => [
                'image' => [
                    'url' => 'https://api.edlib-test.org',
                    'cdnUrl' => 'https://cdn.edlib-test.org',
                    'properties' => [
                        'width' => 2500,
                    ],
                    'searchparams' => [
                        'fallback' => true,
                        'license' => 'all',
                        'pagesize' => 15,
                    ],
                    'modifyDomainPaths' => array_values(
                        array_unique(
                            array_filter(
                                array_map('trim', explode(',', 'https://api.edlib-test.org,https://cdn.edlib-test.org'))
                            )
                        )
                    )
                ]
            ]
        ]);
    }

    public function test_alterImageProperties(): void
    {
        $ndlaImageAdapter = app(NdlaImageAdapter::class);
        $settings = new H5PAlterParametersSettingsDataObject(false);
        $imageProperties = (object)[
            'path' => 'https://api.edlib-test.org/image-api/raw/image.jpg',
            'mime' => 'image/jpeg',
        ];

        $ndlaImageAdapter->alterImageProperties($imageProperties, $settings);
        $this->assertEquals('https://cdn.edlib-test.org/image.jpg', $imageProperties->path);

        $imageProperties->path = 'https://api.edlib-test.org/image-api/raw/id/123?';
        $imageProperties->externalId = 123;

        Cache::shouldReceive('remember')
            ->andReturn((object)['image' => (object)['fileName' => 'image123.jpg']]);

        $ndlaImageAdapter = app(NdlaImageAdapter::class);
        $ndlaImageAdapter->alterImageProperties($imageProperties, $settings);
        $this->assertEquals('https://cdn.edlib-test.org/image123.jpg', $imageProperties->path);
    }

    public function test_isTargetType(): void
    {
        $ndlaImageAdapter = app(NdlaImageAdapter::class);
        $wrongHost = 'https://cdn.some-host.org/image.jpg';
        $correctHost1 = 'https://api.edlib-test.org/image.jpg';
        $correctHost2 = 'https://cdn.edlib-test.org/image.jpg';

        $this->assertFalse($ndlaImageAdapter->isTargetType('application/json', $wrongHost)); // Wrong mime, wrong host
        $this->assertFalse($ndlaImageAdapter->isTargetType('image/jpeg', $wrongHost)); // Correct mime, wrong host
        $this->assertFalse($ndlaImageAdapter->isTargetType('application/json', $correctHost1)); // Wrong mime, correct host
        $this->assertFalse($ndlaImageAdapter->isTargetType('application/json', $correctHost2)); // Wrong mime, correct host

        $this->assertTrue($ndlaImageAdapter->isTargetType('image/jpeg', $correctHost1)); // Correct mime and host
        $this->assertTrue($ndlaImageAdapter->isTargetType('image/jpeg', $correctHost2)); // Correct mime and host
    }
}
