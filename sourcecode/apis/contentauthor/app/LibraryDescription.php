<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class LibraryDescription extends Model
{
    protected $appends = ['capability_id'];

    public function library()
    {
        return $this->belongsTo(H5PLibrary::class);
    }

    public function getCapabilityIdAttribute()
    {
        return $this->library->capability->id;
    }

    public static function getTranslatedName($libraryId, $locale, $fallbackLocale = 'en-gb')
    {
        $name = '';
        $translation = self::where('library_id', $libraryId)->where('locale', $locale)->first();
        if (!$translation) {
            $translation = self::where('library_id', $libraryId)->where('locale', $fallbackLocale)->first();
        }

        if ($translation) {
            $name = $translation->title;
        }

        return $name;
    }
}
