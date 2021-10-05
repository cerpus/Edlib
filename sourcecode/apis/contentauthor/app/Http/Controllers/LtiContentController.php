<?php

namespace App\Http\Controllers;

use App\Content;
use App\Libraries\DataObjects\ResourceDataObject;
use H5PCore;
use Illuminate\Http\Request;

class LtiContentController extends Controller
{
    public function getGroupController($groupName)
    {
        switch ($groupName) {
            case ResourceDataObject::H5P:
                return app('App\Http\Controllers\H5PController');
            case ResourceDataObject::ARTICLE:
                return app('App\Http\Controllers\ArticleController');
            case ResourceDataObject::LINK:
                return app('App\Http\Controllers\LinkController');
            case ResourceDataObject::GAME:
                return app('App\Http\Controllers\GameController');
            case ResourceDataObject::QUESTIONSET:
                return app('App\Http\Controllers\QuestionSetController');
            default:
                return null;
        }
    }

    public function show($id)
    {
        $content = Content::findContentById($id);

        if (empty($content)) {
            return response()->json([
                'code' => 404,
                'message' => 'Content was not found',
            ], 404);
        }

        $controller = $this->getGroupController($content->getContentType());

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

        $controller = $this->getGroupController($content->getContentType());

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
        $controller = $this->getGroupController($type);

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
