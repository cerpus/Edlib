<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\API\Handler\ContentTypeHandler;
use App\Http\Controllers\Controller;
use App\Http\Requests\ApiH5PQuestionsetRequest;
use App\Libraries\H5P\Packages\QuestionSet;
use Illuminate\Http\Response;

class ContentTypeController extends Controller
{
    public function storeH5PQuestionset(ApiH5PQuestionsetRequest $request)
    {
        /** @var ContentTypeHandler $contentTypeHandler */
        $contentTypeHandler = app(ContentTypeHandler::class);
        $content = $contentTypeHandler->storeQuestionset($request->json()->all());
        return response()->json([
            'id' => $content['id'],
            'type' => QuestionSet::$machineName,
        ], Response::HTTP_OK);
    }
}
