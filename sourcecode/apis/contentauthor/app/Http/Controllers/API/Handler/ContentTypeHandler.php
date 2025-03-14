<?php

namespace App\Http\Controllers\API\Handler;

use App\H5PContent;
use App\Http\Requests\H5PStorageRequest;
use App\Libraries\H5P\Helper\H5PPackageProvider;
use App\Libraries\H5P\Packages\H5PBase;
use App\Libraries\H5P\Packages\QuestionSet;
use App\Http\Controllers\H5PController;
use Illuminate\Support\Collection;
use Ramsey\Uuid\Uuid;

class ContentTypeHandler
{
    private $data;

    public function storeQuestionset($questionsetData): H5PContent
    {
        $this->data = $questionsetData;
        $parsedData = $this->parseQuestionsetData();

        /** @var QuestionSet $questionset */
        $questionset = H5PPackageProvider::make(QuestionSet::$machineName);
        $questionsetSemantics = $questionset->populateSemanticsFromData($parsedData);

        $parameters = [
            'title' => $questionsetData['title'],
            'parameters' => json_encode([
                'params' => $questionsetSemantics,
                'metadata' => [], //TODO add support for H5P metadata
            ]),
            'library' => $questionset->getLibraryWithVersion(),
            'license' => $questionsetData['license'],
            'max_score' => array_key_exists('score', $questionsetData) ? $questionsetData['score'] : null,
        ];

        $request = new H5PStorageRequest();
        $request->merge($parameters);

        return $this->storeContentType($request, $questionsetData['authId']);
    }

    private function parseQuestionsetData(): Collection
    {
        return collect($this->data['questions'])->map(function ($element) {
            /** @var H5PBase $contentType */
            $contentType = H5PPackageProvider::make($element['type']);
            $semantics = $contentType->populateSemanticsFromData($element);
            return [
                'semantics' => $semantics,
                'library' => $contentType->getLibraryWithVersion(),
                'subContentId' => Uuid::uuid4(),
                'metadata' => [],
            ];
        });
    }

    private function storeContentType($request, $userId): H5PContent
    {
        /** @var H5PController $h5p */
        $h5p = app(H5PController::class);
        return $h5p->persistContent($request, $userId);
    }
}
