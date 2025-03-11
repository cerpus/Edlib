<?php

namespace App\Http\Controllers\API;

use App\Http\Requests\ApiTranslationRequest;
use App\Libraries\H5P\Dataobjects\H5PTranslationDataObject;
use App\Libraries\H5P\Interfaces\TranslationServiceInterface;
use Illuminate\Http\JsonResponse;

final readonly class TranslationController
{
    public function __construct(
        private TranslationServiceInterface $translator,
    ) {}

    public function __invoke(ApiTranslationRequest $request): JsonResponse
    {
        $fieldsToTranslate = collect($request->validated('fields'))
            ->mapWithKeys(fn($item) => [$item['path'] => $item['value']])
            ->toArray();

        $from = $request->validated('from_lang');
        $to = $request->validated('to_lang');

        $input = new H5PTranslationDataObject($fieldsToTranslate, $from);
        $translated = $this->translator->translate($to, $input);

        return response()->json($translated);
    }
}
