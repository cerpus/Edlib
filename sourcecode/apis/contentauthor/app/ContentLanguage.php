<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ContentLanguage extends Model
{
    protected $fillable = [
        'content_id',
        'language_code',
    ];

    public function setLanguageCodeAttribute($languageCode)
    {
        $languageCode = mb_strtolower($languageCode);

        if (mb_strlen($languageCode) !== 2 && mb_strlen($languageCode) !== 3) {
            throw new \Exception("Please provide a two or three letter ISO 639 language code.");
        }

        $this->attributes['language_code'] = $languageCode;
    }
}
