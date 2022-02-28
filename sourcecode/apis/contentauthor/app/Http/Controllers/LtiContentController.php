<?php

namespace App\Http\Controllers;

use App\Content;
use App\Libraries\ModelRetriever;
use H5PCore;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class LtiContentController extends Controller
{
    public function show($id)
    {
        $content = Content::findContentById($id);

        if (empty($content)) {
            throw new NotFoundHttpException("Content not found");
        }

        $controller = ModelRetriever::getGroupController($content->getContentType());

        if ($controller == null) {
            throw new NotFoundHttpException("Content not found");
        }

        return $controller->ltiShow($content->getId());
    }

    public function edit(Request $request, $id)
    {
        $content = Content::findContentById($id);

        if (empty($content)) {
            throw new NotFoundHttpException("Content not found");
        }

        $controller = ModelRetriever::getGroupController($content->getContentType());

        if ($controller == null) {
            throw new NotFoundHttpException("Content not found");
        }

        return $controller->ltiEdit($request, $content->getId());
    }

    public function create(Request $request, H5PCore $core, $type = Content::TYPE_H5P)
    {
        $controller = ModelRetriever::getGroupController($type);

        if ($controller == null) {
            throw new NotFoundHttpException("Content not found");
        }

        if ($type == Content::TYPE_H5P) {
            return $controller->create($request, $core, null);
        }

        return $controller->ltiCreate($request);
    }
}
