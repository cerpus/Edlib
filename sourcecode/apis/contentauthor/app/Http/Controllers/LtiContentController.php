<?php

namespace App\Http\Controllers;

use App\Content;
use App\Libraries\DataObjects\ResourceDataObject;
use App\Libraries\ModelRetriever;
use H5PCore;
use Illuminate\Http\Request;

class LtiContentController extends Controller
{
    public function show($id)
    {
        $content = Content::findContentById($id);

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

        return $controller->ltiShow($content->getId());
    }

    public function edit(Request $request, $id)
    {
        $content = Content::findContentById($id);

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
                'message' => 'Editor for content was not found',
            ], 404);
        }

        return $controller->ltiEdit($request, $content->getId());
    }

    public function create(Request $request, H5PCore $core, $type = ResourceDataObject::H5P)
    {
        $controller = ModelRetriever::getGroupController($type);

        if ($controller == null) {
            return response()->json([
                'code' => 404,
                'message' => 'Content type was not found',
            ], 404);
        }

        if ($type == ResourceDataObject::H5P) {
            return $controller->create($request, $core, null);
        }

        return $controller->ltiCreate($request);
    }
}
