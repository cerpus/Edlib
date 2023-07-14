<?php

namespace Tests\Integration\Libraries\H5P;

use App\Libraries\H5P\H5PCreateConfig;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class H5PConfigAbstractTest extends TestCase
{
    use RefreshDatabase;

    public function test_setUserName(): void
    {
        $data = app(H5PCreateConfig::class)
            ->setUserName('Emily Quackfaster')
            ->getConfig();

        $this->assertSame('Emily Quackfaster', $data->user->name);
    }

    public function test_setUserId(): void
    {
        $data = app(H5PCreateConfig::class)
            ->setUserId('9071ace1-79ab-4c26-9255-69ea29fa74d1')
            ->getConfig();

        $this->assertSame('9071ace1-79ab-4c26-9255-69ea29fa74d1', $data->user->name);
    }

    public function test_setUserUserName(): void
    {
        $data = app(H5PCreateConfig::class)
            ->setUserUsername('QuackMaster')
            ->getConfig();

        $this->assertSame('QuackMaster', $data->user->name);
    }

    public function test_setUserEmail(): void
    {
        $data = app(H5PCreateConfig::class)
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
}
