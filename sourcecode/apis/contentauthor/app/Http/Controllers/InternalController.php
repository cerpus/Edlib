<?php

namespace App\Http\Controllers;

use App\Content;
use App\Libraries\ModelRetriever;
use Illuminate\Http\Request;

class InternalController extends Controller
{
    public function view(Request $request)
    {
        $content = Content::findContentById($request->get('externalSystemId'));

        if (empty($content)) {
            return response()->json([
                'code' => 404,
                'message' => 'Content was not found',
            ], 404);
        }

        $controller = ModelRetriever::getGroupController($content->getContentType());

        if ($controller == null) {
            return response()->json([
                'code' => 404,
                'message' => 'Content was not found',
            ], 404);
        }

        $contextKey = 'something';

        return $controller->doShow($content->getId(), $contextKey, false);
    }
}
