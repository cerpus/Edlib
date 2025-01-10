<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\Session;
use App\ContentLanguage;

/**
 * @property string|int $id
 */
trait HasLanguage
{
    /**
     * @return HasOne<ContentLanguage, $this>
     */
    public function language(): HasOne
    {
        return $this->hasOne(ContentLanguage::class, 'content_id');
    }

    public function getLanguage()
    {
        $contentLanguage = ContentLanguage::firstOrCreate(
            [
                'content_id' => $this->id,
            ],
            [
                'language_code' => $this->removeCountryCode(Session::get('locale', config('app.fallback_locale'))),
            ],
        );

        return $contentLanguage->language_code;
    }

    public function setLanguage($language)
    {
        if ($language === 'unknown') {
            $language = 'nb';
        }

        $contentLanguage = ContentLanguage::updateOrCreate(
            [
                'content_id' => $this->id,
            ],
            [
                'language_code' => $language,
            ],
        );

        return $contentLanguage;
    }

    private function removeCountryCode($language)
    {
        return explode('-', $language)[0];
    }
}
