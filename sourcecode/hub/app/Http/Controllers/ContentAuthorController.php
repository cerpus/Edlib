<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\ContentInfoRequest;
use App\Http\Requests\DeepLinkingReturnRequest;
use App\Models\Content;
use App\Models\ContentVersion;
use App\Models\LtiTool;
use App\Models\User;
use Cerpus\EdlibResourceKit\Lti\Edlib\DeepLinking\EdlibLtiLinkItem;
use Cerpus\EdlibResourceKit\Lti\Lti11\Mapper\DeepLinking\ContentItemsMapperInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use RuntimeException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ContentAuthorController extends Controller
{
    public function info(ContentInfoRequest $request, LtiTool $tool): JsonResponse
    {
        $data = $request->validated();
        $response = [];
        if (array_key_exists('lti_launch_url', $data)) {
            $versions = $tool->contentVersions()
                ->where('lti_launch_url', $request->validated('lti_launch_url'))
                ->get();

            if ($versions->count() === 0) {
                throw new NotFoundHttpException();
            } elseif ($versions->count() > 1) {
                throw new RuntimeException('Multiple versions found for content');
            }

            $version = $versions->firstOrFail();
            $response = [
                'id' => $version->content_id,
                'version_id' => $version->id,
                'update_url' => route('author.content.update', [
                    $version->lti_tool_id,
                    $version->content_id,
                    $version->id,
                ]),
            ];

        } elseif (array_key_exists('content_url', $data)) {
            $route = Route::getRoutes()->match(request()->create($data['content_url']));
            if ($route && in_array($route->getName(), ['content.details', 'content.version-details', 'content.embed', 'content.share'])) {
                $contentId = $route->parameter('content');
                $inputVersion = $tool->contentVersions()
                    ->where('content_id', $contentId)
                    ->first();
                if (!$inputVersion) {
                    throw new NotFoundHttpException('No content found for url');
                }
                $latest = $inputVersion->content?->getCachedLatestVersion();
                if (!$latest) {
                    throw new NotFoundHttpException('Could not find lastest version');
                }
                $response = [
                    'id' => $latest->content_id,
                    'version_id' => $latest->id,
                    'lti_launch_url' => $latest->lti_launch_url,
                ];
            } else {
                throw new BadRequestHttpException('Not an allowed url');
            }
        } elseif (array_key_exists('content_or_version_id', $data)) {
            $inputVersion = $tool->contentVersions()
                ->where('content_id', $data['content_or_version_id'])
                ->orwhere('id', $data['content_or_version_id'])
                ->first();
            if (!$inputVersion) {
                throw new NotFoundHttpException('No content found for id');
            }
            $latest = $inputVersion->content?->getCachedLatestVersion();
            if (!$latest) {
                throw new NotFoundHttpException('Could not find lastest version');
            }
            $response = [
                'id' => $latest->content_id,
                'version_id' => $latest->id,
                'lti_launch_url' => $latest->lti_launch_url,
            ];
        }

        return response()->json($response);
    }

    public function update(
        LtiTool $tool,
        Content $content,
        ContentVersion $version,
        DeepLinkingReturnRequest $request,
        ContentItemsMapperInterface $mapper,
    ): JsonResponse {
        $item = $mapper->map($request->input('content_items'))[0];
        assert($item instanceof EdlibLtiLinkItem);

        $user = User::findOrFail($request->get('user_id'));
        assert($user instanceof User);

        $item = $item->withPublished($version->published);

        $version = DB::transaction(function () use ($content, $version, $item, $tool, $user) {
            $previousVersion = $version;

            $version = $content->createVersionFromLinkItem($item, $tool, $user);
            $version->previousVersion()->associate($previousVersion);
            $version->save();

            if ($item->isShared() !== null) {
                $content->shared = $item->isShared();
                $content->save();
            }

            return $version;
        });
        assert($version instanceof ContentVersion);

        return response()->json(
            [
                'id' => $version->content_id,
                'version_id' => $version->id,
            ],
            Response::HTTP_CREATED,
        );
    }
}
