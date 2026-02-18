<?php

declare(strict_types=1);

namespace App\Libraries\H5P\TranslationServices;

use App\Libraries\H5P\Dataobjects\H5PTranslationDataObject;
use App\Libraries\H5P\Interfaces\TranslationServiceInterface;
use LogicException;

final class NullTranslationAdapter implements TranslationServiceInterface
{
    public function getSupportedLanguages(): array|null
    {
        return [];
    }

    public function translate(string $toLanguage, H5PTranslationDataObject $data): H5PTranslationDataObject
    {
        throw new LogicException('This should not have been called');
    }
}
