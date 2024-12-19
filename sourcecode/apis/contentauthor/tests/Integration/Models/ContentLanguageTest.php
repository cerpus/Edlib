<?php

namespace Tests\Integration\Models;

use App\ContentLanguage;
use Exception;
use Generator;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class ContentLanguageTest extends TestCase
{
    #[DataProvider('codeProvider')]
    public function testLanguageCodeWillFailIfLanguageIsNotTwoOrThreeCharacter(string $code)
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage("Please provide a two or three letter ISO 639 language code.");

        $contentLanguage = new ContentLanguage();
        $contentLanguage->language_code = $code;
    }

    #[DataProvider('codeProvider')]
    public function testLanguageCodeWillFailIfLanguageIsNotTwoOrThreeCharactersMassAssignment(string $code)
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage("Please provide a two or three letter ISO 639 language code.");

        new ContentLanguage([
            'language_code' => $code,
        ]);
    }

    public static function codeProvider(): Generator
    {
        yield['e'];
        yield['engl'];
    }
}
