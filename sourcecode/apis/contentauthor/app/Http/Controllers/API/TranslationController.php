<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Libraries\H5P\Dataobjects\H5PTranslationDataObject;
use App\Libraries\H5P\Interfaces\TranslationServiceInterface;
use Illuminate\Http\Request;

class TranslationController extends Controller
{
    public function __invoke(Request $request, TranslationServiceInterface $translationService)
    {
        $fieldsToTranslate = $request->json();
        if ($fieldsToTranslate->has('fields')) {
            $translationDataObject = H5PTranslationDataObject::create();
            $fieldsMapping = collect($fieldsToTranslate->get('fields'))
                ->mapWithKeys(function ($item) {
                    return [$item['path'] => $item['value']];
                })->toArray();
            $translationDataObject->setFieldsFromArray($fieldsMapping);
            $translated = $translationService->getTranslations($translationDataObject);
            return response()->json($translated->toArray());
        }
    }
}
