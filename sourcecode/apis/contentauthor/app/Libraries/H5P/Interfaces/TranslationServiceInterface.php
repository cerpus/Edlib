<?php


namespace App\Libraries\H5P\Interfaces;


use App\Libraries\H5P\Dataobjects\H5PTranslationDataObject;

interface TranslationServiceInterface
{
    public function getTranslations(H5PTranslationDataObject $data): H5PTranslationDataObject;
}
