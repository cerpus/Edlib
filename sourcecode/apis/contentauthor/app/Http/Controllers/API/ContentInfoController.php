<?php

namespace App\Http\Controllers\API;

use App\Article;
use App\CollaboratorContext;
use App\Content;
use App\EdlibResource\CaEdlibResource;
use App\EdlibResource\ResourceSerializer;
use App\Game;
use App\H5PContent;
use App\Http\Controllers\Controller;
use App\Libraries\ModelRetriever;
use App\QuestionSet;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ContentInfoController extends Controller
{
    public function __construct(private ResourceSerializer $resourceSerializer)
    {
    }

    public function index($id): JsonResponse
    {
        $content = Content::findContentById($id);

        if (empty($content)) {
            return response()->json([
                'code' => 404,
                'message' => 'Content was not found',
            ], 404);
        }

        return response()->json(
            $this->resourceSerializer->serialize($content->getEdlibDataObject()),
        );
    }

    public function list(Request $request): JsonResponse
    {
        $offset = $request->input("offset", 0);
        $limit = $request->input("limit", 50);
        /** @var Article|Game|QuestionSet|H5PContent $model */
        $model = ModelRetriever::getModelFromGroup($request->input("group"));

        $modelResources = $model::select()
            ->orderBy("created_at", "ASC")
            ->limit($limit)
            ->offset($offset)
            ->get()
        ;

        $resources = [];

        foreach ($modelResources as $modelResource) {
            /** @var Article|Game|QuestionSet|H5PContent $modelResource */
            $resources[] = new CaEdlibResource(
                strval($modelResource->id),
                $modelResource->title,
                $modelResource->getContentOwnerId(),
                !$modelResource->inDraftState(),
                $modelResource->isPublished(),
                $modelResource->getISO6393Language(),
                $modelResource->getContentType(true),
                $modelResource->license,
                $modelResource->getMaxScore(),
                $modelResource->created_at->toDateTimeImmutable(),
                $modelResource->updated_at->toDateTimeImmutable(),
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
            );
        }
        $response = [
            "pagination" => [
                "totalCount" => $model::count(),
                "offset" => $offset,
                "limit" => $limit
            ],
            "resources" => array_map(
                fn(CaEdlibResource $resource) => $this->resourceSerializer->serialize($resource),
                $resources,
            ),
        ];

        return response()->json($response);
    }

    public function getContentTypeInfo(string $contentType): JsonResponse
    {
        /** @var Article|Game|QuestionSet|H5PContent $model */
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
