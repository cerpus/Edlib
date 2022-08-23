<?php

namespace Tests\Unit\Libraries\H5P;

use App\Libraries\H5P\LtiToH5PLanguage;
use Generator;
use PHPUnit\Framework\TestCase;

class LtiToH5PLanguageTest extends TestCase
{
    /** @dataProvider dataset */
    public function testConvert($expected, $input = null, $default = null): void
    {
        $this->assertEquals($expected, LtiToH5PLanguage::convert($input, $default));
    }

    public function dataset(): Generator
    {
        yield 'nb-nb' => ['nb', 'nb-no'];
        yield 'nn' => ['nn', 'nn'];
        yield 'sv' => ['sv', 'finsk', 'sv-sv'];
        yield 'ko' => ['ko', '', 'ko-kr'];
        yield 'de' => ['de', '', 'de'];
        yield 'de_de' => ['de', 'DE_DE'];
        yield 'sm' => ['sm', 'samisk', 'SM_SM'];
        yield 'en_default' => ['en', ''];
        yield 'en_empty' => ['en', '', ''];
        yield 'en_null' => ['en'];
        yield 'n' => ['en', 'n'];
        yield 's' => ['en', 'n', 's'];
        yield 'k' => ['en', '', 'k'];
    }
}
