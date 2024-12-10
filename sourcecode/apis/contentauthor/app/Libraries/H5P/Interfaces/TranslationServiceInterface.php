<?php

namespace App\Libraries\H5P\Interfaces;

use App\Libraries\H5P\Dataobjects\H5PTranslationDataObject;

interface TranslationServiceInterface
{
    /**
     * @return array<string, string[]>|null
     *     An array where the keys are the supported source languages, and the
     *     values are an array of languages to which the source can be
     *     translated. If the supported languages aren't known in advance, NULL
     *     is returned.
     */
    public function getSupportedLanguages(): array|null;

    public function translate(
        string $toLanguage,
        H5PTranslationDataObject $data,
    ): H5PTranslationDataObject;
}
