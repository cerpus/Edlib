<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\ContentInfoRequest;
use App\Http\Requests\DeepLinkingReturnRequest;
use App\Models\Content;
use App\Models\ContentVersion;
use App\Models\LtiTool;
use App\Models\Tag;
use App\Models\User;
use Cerpus\EdlibResourceKit\Lti\Edlib\DeepLinking\EdlibLtiLinkItem;
use Cerpus\EdlibResourceKit\Lti\Lti11\Mapper\DeepLinking\ContentItemsMapperInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use RuntimeException;
use Symfony\Component\HttpFoundation\Response;
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
                    ->whereColumn('cv2.previous_version_id', 'content_versions.id');
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

        $launchUrls = $query->pluck('lti_launch_url');

        return response()->json([
            'data' => $launchUrls->map(fn (string $url) => ['lti_launch_url' => $url])->values(),
        ]);
    }

    public function info(ContentInfoRequest $request, LtiTool $tool): JsonResponse
    {
        $versions = $tool->contentVersions()
            ->where('lti_launch_url', $request->validated('lti_launch_url'))
            ->get();

        if ($versions->count() === 0) {
            throw new NotFoundHttpException();
        } elseif ($versions->count() > 1) {
            throw new RuntimeException('Multiple versions found for content');
        }

        $version = $versions->firstOrFail();

        return response()->json([
            'id' => $version->content_id,
            'version_id' => $version->id,
            'update_url' => route('author.content.update', [
                $version->lti_tool_id,
                $version->content_id,
                $version->id,
            ]),
        ]);
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
