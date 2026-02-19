<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\ContentExclusionRequest;
use App\Http\Requests\ContentInfoRequest;
use App\Http\Requests\DeepLinkingReturnRequest;
use App\Models\Content;
use App\Models\ContentExclusion;
use App\Models\ContentVersion;
use App\Models\LtiTool;
use App\Models\Tag;
use App\Models\User;
use App\EdlibResourceKit\Lti\Edlib\DeepLinking\EdlibLtiLinkItem;
use App\EdlibResourceKit\Lti\Lti11\Mapper\DeepLinking\ContentItemsMapperInterface;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use RuntimeException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ContentAuthorController extends Controller
{
    public function leaves(Request $request, LtiTool $tool): JsonResponse
    {
        $query = ContentVersion::query()
            ->where('lti_tool_id', $tool->id)
            ->whereNotExists(function ($sub) {
                $sub->select(DB::raw(1))
                    ->from('content_versions as cv2')
                    ->whereColumn('cv2.previous_version_id', 'content_versions.id')
                    ->whereColumn('cv2.content_id', 'content_versions.content_id');
            });

        if ($request->has('tag')) {
            ['prefix' => $prefix, 'name' => $name] = Tag::parse($request->input('tag'));

            $query->whereExists(function ($sub) use ($prefix, $name) {
                $sub->select(DB::raw(1))
                    ->from('content_version_tag')
                    ->join('tags', 'tags.id', '=', 'content_version_tag.tag_id')
                    ->whereColumn('content_version_tag.content_version_id', 'content_versions.id')
                    ->where('tags.prefix', strtolower($prefix))
                    ->where('tags.name', strtolower($name));
            });
        }

        $versions = $query->get();

        return response()->json([
            'data' => $versions->map(fn (ContentVersion $version) => [
                'lti_launch_url' => $version->lti_launch_url,
                'title' => $version->title,
                'content_id' => $version->content_id,
                'update_url' => route('author.content.update', [
                    $version->lti_tool_id,
                    $version->content_id,
                    $version->id,
                ]),
            ])->values(),
        ]);
    }

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

    public function listExclusions(ContentExclusionRequest $request, LtiTool $tool): JsonResponse
    {
        $excludeFrom = $request->validated('exclude_from');

        $exclusions = ContentExclusion::query()
            ->where('exclude_from', $excludeFrom)
            ->whereHas('content', function ($query) use ($tool) {
                $query->whereHas('versions', function ($query) use ($tool) {
                    $query->where('lti_tool_id', $tool->id); // @phpstan-ignore argument.type
                });
            })
            ->with(['content.latestVersion'])
            ->get();

        return response()->json([
            'data' => $exclusions->map(function (ContentExclusion $exclusion) {
                $version = $exclusion->content?->latestVersion;

                return [
                    'content_id' => $exclusion->content_id,
                    'exclude_from' => $exclusion->exclude_from,
                    'lti_launch_url' => $version?->lti_launch_url,
                    'title' => $version?->title,
                ];
            })->values(),
        ]);
    }

    public function addExclusions(ContentExclusionRequest $request, LtiTool $tool): JsonResponse
    {
        $contentIds = $request->validated('content_ids');
        $excludeFrom = $request->validated('exclude_from');
        $userId = $request->validated('user_id');

        $added = 0;
        foreach ($contentIds as $contentId) {
            try {
                ContentExclusion::create([
                    'content_id' => $contentId,
                    'exclude_from' => $excludeFrom,
                    'user_id' => $userId,
                ]);
                $added++;
            } catch (UniqueConstraintViolationException) {
                // Already excluded, skip
            }
        }

        return response()->json(['added' => $added]);
    }

    public function deleteExclusions(ContentExclusionRequest $request, LtiTool $tool): JsonResponse
    {
        $contentIds = $request->validated('content_ids');
        $excludeFrom = $request->validated('exclude_from');

        $deleted = ContentExclusion::query()
            ->whereIn('content_id', $contentIds)
            ->where('exclude_from', $excludeFrom)
            ->delete();

        return response()->json(['deleted' => $deleted]);
    }
}
