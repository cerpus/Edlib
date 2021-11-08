<?php

namespace App\Http\Controllers\API;

use App\CollaboratorContext;
use App\Content;
use App\Http\Controllers\Controller;
use App\Http\Libraries\License;
use App\Libraries\DataObjects\EdlibResourceDataObject;
use App\Libraries\ModelRetriever;
use Illuminate\Http\Request;

class ContentInfoController extends Controller
{
    public function index($id)
    {
        $content = Content::findContentById($id);

        if (empty($content)) {
            return response()->json([
                'code' => 404,
                'message' => 'Content was not found',
            ], 404);
        }

        return response()->json($content->getEdlibDataObject());
    }

    public function list(Request $request)
    {
        $offset = $request->get("offset", 0);
        $limit = $request->get("limit", 50);
        $model = ModelRetriever::getModelFromGroup($request->get("group"));

        $preFetchIds = $model::select("id")
            ->orderBy("created_at", "ASC")
            ->limit($limit)
            ->offset($offset)
            ->get()
            ->map(function ($row) {
                return $row->id;
            });

        $modelResources = $model::findMany($preFetchIds);

        $resources = [];

        $contentIds = $modelResources->map(function ($modelResource) {
            return strval($modelResource->id);
        })->toArray();

        $lic = app()->make(License::class);

        $licenses = $lic->getLicensesByContentId($contentIds);

        foreach ($modelResources as $modelResource) {
            $actualLicense = null;
            foreach ($licenses as $license) {
                if ($license->id == strval($modelResource->id)) {
                    $actualLicense = $license->license;
                }
            }

            $resources[] = new EdlibResourceDataObject(
                strval($modelResource->id),
                $modelResource->title,
                $modelResource->getContentOwnerId(),
                $modelResource->isPublished(),
                !$modelResource->inDraftState(),
                $modelResource->getISO6393Language(),
                $modelResource->getContentType(true),
                $actualLicense,
                $modelResource->getMaxScore(),
                $modelResource->created_at,
                $modelResource->updated_at,
                CollaboratorContext::getResourceContextCollaborators($modelResource->id),
                $modelResource->collaborators
                    ->map(function ($collaborator) {
                        return strtolower($collaborator->email);
                    })->filter(function ($email) {
                        return $email != "";
                    })
                    ->sort()
                    ->all(),
                $modelResource->getAuthorOverwrite()
            );;
        }
        $response = [
            "pagination" => [
                "totalCount" => $model::count(),
                "offset" => $offset,
                "limit" => $limit
            ],
            "resources" => $resources
        ];

        return response()->json($response);
    }

    public function getContentTypeInfo(string $contentType)
    {
        $model = ModelRetriever::getModelFromContentType($contentType);

        $library = $model::getContentTypeInfo($contentType);

        if (!$library) {
            return response()->json([
                "message" => "Content type info not found"
            ], 404);
        }

        return response()->json([
            'contentType' => $library
        ]);
    }
}
