<?php

namespace Tests\Integration\Libraries\H5P;

use App\H5PContent;
use App\H5PLibrary;
use App\Libraries\H5P\H5PConfigAbstract;
use App\Libraries\H5P\H5PCreateConfig;
use App\Libraries\H5P\Interfaces\H5PAdapterInterface;
use H5PCore;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\MockObject\MockObject;
use Tests\TestCase;

class H5PConfigAbstractTest extends TestCase
{
    use RefreshDatabase;

    private H5PConfigAbstract|MockObject $h5PConfig;
    private H5PCore $h5pCore;

    public function setUp(): void
    {
        parent::setUp();

        $this->h5pCore = app(H5PCore::class);
        $this->h5PConfig = $this->getMockForAbstractClass(
            H5PConfigAbstract::class,
            [
                app(H5PAdapterInterface::class),
                $this->h5pCore,
            ]
        );
    }

    public function test_setUserName(): void
    {
        $data = $this->h5PConfig
            ->setUserName('Emily Quackfaster')
            ->getConfig();

        $this->assertSame('Emily Quackfaster', $data->user->name);
    }

    public function test_setUserId(): void
    {
        $data = $this->h5PConfig
            ->setUserId('9071ace1-79ab-4c26-9255-69ea29fa74d1')
            ->getConfig();

        $this->assertSame('9071ace1-79ab-4c26-9255-69ea29fa74d1', $data->user->name);
    }

    public function test_setUserUserName(): void
    {
        $data = $this->h5PConfig
            ->setUserUsername('QuackMaster')
            ->getConfig();

        $this->assertSame('QuackMaster', $data->user->name);
    }

    public function test_setUserEmail(): void
    {
        $data = $this->h5PConfig
            ->setUserEmail('eq@duckburg.quack')
            ->getConfig();

        $this->assertSame('eq@duckburg.quack', $data->user->mail);
    }

    public function test_setLanguage(): void
    {
        $data = app(H5PCreateConfig::class)
            ->setLanguage('nb')
            ->getConfig();

        $this->assertSame('nb', $data->editor->language);
    }

    public function test_setRedirectToken(): void
    {
        $data = app(H5PCreateConfig::class)
            ->setRedirectToken('theRedirectToken')
            ->getConfig();

        $this->assertSame('/ajax?redirectToken=theRedirectToken&h5p_id=&action=', $data->editor->ajaxPath);
    }

    public function test_getH5PCore(): void
    {
        $h5pCore = $this->h5PConfig->getH5PCore();

        $this->assertSame($this->h5pCore, $h5pCore);
        $this->assertSame($this->h5PConfig->h5pCore, $h5pCore);
    }

    public function test_content(): void
    {
        /** @var H5PLibrary $library */
        $library = H5PLibrary::factory()->create();
        /** @var H5PContent $content */
        $content = H5PContent::factory()->create([
            'library_id' => $library->id,
        ]);

        $h5pConfig = $this->h5PConfig->loadContent($content->id);

        $this->assertSame($h5pConfig, $this->h5PConfig);
        $this->assertSame($content->id, $h5pConfig->id);

        $data = $h5pConfig->getContent();
        $this->assertSame($content->id, $data['id']);
        $this->assertSame($library->id, $data['library']['id']);
    }
}
