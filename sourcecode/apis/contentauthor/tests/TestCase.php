<?php

namespace Tests;

use Illuminate\Foundation\Mix;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\HtmlString;

abstract class TestCase extends BaseTestCase
{
    private static Mix $fakeMix;

    protected function setUp(): void
    {
        parent::setUp();

        // Enable testing without building manifests
        $this->instance(Mix::class, self::getFakeMix());
    }

    /**
     * Fake Mix that doesn't care whether a file exists or not. This allows
     * running tests without building frontend assets.
     */
    private static function getFakeMix(): Mix
    {
        return self::$fakeMix ??= new class extends Mix {
            public function __invoke($path, $manifestDirectory = ''): HtmlString
            {
                $path = rtrim($manifestDirectory, '/') . '/' . ltrim($path, '/');

                return new HtmlString($path);
            }
        };
    }
}
