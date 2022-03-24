<?php

namespace Tests;

use Illuminate\Foundation\Mix;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\HtmlString;
use Tests\Traits\WithFaker;
use Tests\Traits\MockMQ;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    private static Mix $fakeMix;

    protected function setUp(): void
    {
        parent::setUp();

        // Enable testing without building manifests
        $this->instance(Mix::class, self::getFakeMix());
    }

    public function setUpTraits()
    {
        parent::setUpTraits();

        $uses = array_flip(class_uses_recursive(static::class));

        if (isset($uses[WithFaker::class])) {
            $this->setUpFaker();
        }

        if (isset($uses[MockMQ::class])) {
            $this->setUpMockMQ();
        }
    }

    /**
     * Fake Mix that doesn't care whether a file exists or not. This allows
     * running tests without building frontend assets.
     */
    private static function getFakeMix(): Mix
    {
        return self::$fakeMix ??= new class() extends Mix {
            public function __invoke($path, $manifestDirectory = ''): HtmlString
            {
                $path = rtrim($manifestDirectory, '/').'/'.ltrim($path, '/');

                return new HtmlString($path);
            }
        };
    }
}
