<?php

namespace Tests\Unit\Models;

use Tests\TestCase;
use App\ContentLanguage;

class ContentLanguageTest extends TestCase
{
    public function testLanguageCodeWillFailIfLanguageIsNotTwoOrThreeCharacters_OneCharacter()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage("Please provide a two or three letter ISO 639 language code.");

        $contentLanguage = new ContentLanguage();
        $contentLanguage->language_code = 'e';
    }

    public function testLanguageCodeWillFailIfLanguageIsNotTwoOrThreeCharacters_FourCharacters()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage("Please provide a two or three letter ISO 639 language code.");

        $contentLanguage = new ContentLanguage();
        $contentLanguage->language_code = 'engl';
    }

    public function testLanguageCodeWillFailIfLanguageIsNotTwoOrThreeCharactersMassAssignment_FourCharacters()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage("Please provide a two or three letter ISO 639 language code.");

        $contentLanguage = new ContentLanguage([
            'language_code' => 'engl'
        ]);
    }

    public function testLanguageCodeWillFailIfLanguageIsNotTwoOrThreeCharactersMassAssignment_OneCharacter()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage("Please provide a two or three letter ISO 639 language code.");

        $contentLanguage = new ContentLanguage([
            'language_code' => 'e'
        ]);
    }

}


