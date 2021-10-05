<?php

namespace App\Http\Controllers\API\Handler;


use App\Content;
use App\Libraries\H5P\Helper\H5PPackageProvider;
use App\Libraries\H5P\Packages\H5PBase;
use App\Libraries\H5P\Packages\QuestionSet;
use App\Http\Controllers\H5PController;
use Illuminate\Http\Request;
use Ramsey\Uuid\Uuid;

class ContentTypeHandler
{

    private $data;

    public function storeQuestionset($questionsetData)
    {
        $this->data = $questionsetData;
        $parsedData = $this->parseQuestionsetData();

        /** @var H5PBase $questionset */
        $questionset = H5PPackageProvider::make(QuestionSet::$machineName);
        $questionsetSemantics = $questionset->populateSemanticsFromData($parsedData);

        $parameters = [
            'title' => $questionsetData['title'],
            'parameters' => json_encode([
                'params' => $questionsetSemantics,
                'metadata' => [], //TODO add support for H5P metadata
            ]),
            'library' => $questionset->getLibraryWithVersion(),
            'share' => $questionsetData['sharing'] === true || $questionsetData['sharing'] === "share" ? "share" : "private",
            'license' => $questionsetData['license'],
            'max_score' => array_key_exists('score', $questionsetData) ? $questionsetData['score'] : null,
            'isPublished' => Content::isDraftLogicEnabled() && array_key_exists('published', $questionsetData) ? $questionsetData['published'] : 1,
        ];

        $request = app(Request::class);
        $request->merge($parameters);

        return $this->storeContentType($request, $questionsetData['authId']);
    }

    private function parseQuestionsetData()
    {
        return collect($this->data['questions'])->map(function ($element) {
            /** @var H5PBase $contentType */
            $contentType = H5PPackageProvider::make($element['type']);
            $semantics = $contentType->populateSemanticsFromData($element);
            return [
                'semantics' => $semantics,
                'library' => $contentType->getLibraryWithVersion(),
                'subContentId' => Uuid::uuid4(),
                'metadata' => []
            ];
        });
    }

    private function storeContentType($request, $userId)
    {
        /** @var H5PController $h5p */
        $h5p = app(H5PController::class);
        return $h5p->persistContent($request, $userId)->toArray();
    }

}
