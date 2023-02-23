<?php

namespace App\Http\Controllers\API;

use App\Http\Requests\ApiTranslationRequest;
use App\Libraries\H5P\Dataobjects\H5PTranslationDataObject;
use App\Libraries\H5P\Interfaces\TranslationServiceInterface;
use Illuminate\Http\JsonResponse;

final class TranslationController
{
    public function __construct(
        private readonly TranslationServiceInterface $translationService,
    ) {
    }

    public function __invoke(ApiTranslationRequest $request): JsonResponse
    {
        $fieldsToTranslate = collect($request->validated('fields'))
            ->mapWithKeys(fn ($item) => [$item['path'] => $item['value']])
            ->toArray();

        $input = new H5PTranslationDataObject($fieldsToTranslate);
        $translated = $this->translationService->getTranslations($input);

        return response()->json($translated);
    }
}
