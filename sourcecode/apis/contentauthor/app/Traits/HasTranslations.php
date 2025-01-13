<?php

namespace App\Traits;

use App\Content;
use App\ContentLanguageLink;

trait HasTranslations
{
    public function getTranslations(): array
    {
        $translations = ContentLanguageLink::where('main_content_id', $this->id)
            ->orWhere('link_content_id', $this->id)
            ->get()
            ->map(function ($translation) {
                if (!$translation->link_content_id) {
                    return $translation->main_content_id;
                }
                return $translation->link_content_id;
            })
            ->values();

        $content = self::whereIn('id', $translations)
            ->get()
            ->map(function ($content) {
                return (object) ['language' => $content->getLanguage(), 'content' => $content];
            })
            ->sortBy('language')
            ->toArray();

        return $content;
    }

    public function setAsMasterTranslation(): bool
    {
        $result = ContentLanguageLink::updateOrCreate(
            [
                'main_content_id' => $this->id,
            ],
            [
                'link_content_id' => null,
                'content_type' => $this->getContentType(),
                'language_code' => $this->getLanguage(),
            ],
        );

        return $result ? true : false;
    }

    public function setAsTranslationOf(Content $content): bool
    {
        $masterExist = ContentLanguageLink::where('main_content_id', $content->id)->where('link_content_id', null)->exists();
        if ($masterExist) {
            $result = ContentLanguageLink::updateOrCreate(
                [
                    'main_content_id' => $content->id,
                    'link_content_id' => $this->id,
                ],
                [
                    'content_type' => $this->getContentType(),
                    'language_code' => $this->getLanguage(),
                ],
            );
        }

        return ($masterExist && $result);
    }

    public function hasTranslations(): bool
    {
        $translations = ContentLanguageLink::where('main_content_id', $this->id)
            ->orWhere('link_content_id', $this->id)
            ->select('id')
            ->get();

        if (is_countable($translations)) {
            return count($translations) > 1;
        }

        return false;
    }
}
