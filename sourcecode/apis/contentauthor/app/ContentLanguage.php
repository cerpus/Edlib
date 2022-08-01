<?php

namespace App;

use Exception;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $content_id
 * @property string $language_code
 *
 * @method static firstOrCreate(array $attributes = [], array $values = [])
 */

class ContentLanguage extends Model
{
    protected $fillable = [
        'content_id',
        'language_code',
    ];

    /**
     * @throws Exception
     */
    public function setLanguageCodeAttribute($languageCode)
    {
        $languageCode = mb_strtolower($languageCode);

        if (mb_strlen($languageCode) !== 2 && mb_strlen($languageCode) !== 3) {
            throw new Exception("Please provide a two or three letter ISO 639 language code.");
        }

        $this->attributes['language_code'] = $languageCode;
    }
}
