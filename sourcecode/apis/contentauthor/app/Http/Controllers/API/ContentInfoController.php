<?php

namespace App\Http\Controllers\API;

use App\Article;
use App\CollaboratorContext;
use App\Content;
use App\ContentVersion;
use App\EdlibResource\CaEdlibResource;
use App\EdlibResource\ResourceSerializer;
use App\Game;
use App\H5PContent;
use App\Http\Controllers\Controller;
use App\Libraries\ModelRetriever;
use App\QuestionSet;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

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
                $modelResource->isPublished(),
                $modelResource->isDraft(),
                $modelResource->isListed(),
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
                    ->values()
                    ->toArray(),
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
                fn (CaEdlibResource $resource) => $this->resourceSerializer->serialize($resource),
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

    public function getVersion(string $id): JsonResponse
    {
        $content = Content::findContentById($id);
        if ($content !== null && $content->version_id !== null) {
            $version = $content->getVersion();
            return response()->json([
                'id' => $version->id,
                'versionPurpose' => $version->version_purpose,
                'externalSystemId' => $version->content_id,
                'externalSystemName' => 'contentauthor',
            ]);
        }

        return response()->json(['Version data not found'], 404);
    }

    public function getPreviousVersions(ContentVersion $version): JsonResponse
    {
        return response()->json(
            $this->collectPreviousVersions(collect(), $version->previousVersion)
        );
    }

    private function collectPreviousVersions(Collection $collection, ?ContentVersion $version): Collection
    {
        if ($version === null) {
            return $collection;
        }

        $collection->push([
            'externalSystem' => 'contentauthor',
            'externalReference' => $version->content_id,
        ]);

        if ($version->version_purpose !== ContentVersion::PURPOSE_UPDATE) {
            return $collection;
        }

        $previousVersion = $version->previousVersion;
        if ($previousVersion === null) {
            return $collection;
        }

        return $this->collectPreviousVersions($collection, $previousVersion);
    }
}
