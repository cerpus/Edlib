<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Requests\Api\ContentRequest;
use App\Models\Content;
use App\Models\Tag;
use App\Transformers\ContentTransformer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use League\Fractal\Pagination\IlluminatePaginatorAdapter;

use function fractal;
use function response;

final readonly class ContentController
{
    public function __construct(private ContentTransformer $transformer) {}

    public function index(): JsonResponse
    {
        $contents = Content::paginate();

        return fractal($contents)
            ->transformWith($this->transformer)
            ->paginateWith(new IlluminatePaginatorAdapter($contents))
            ->respond();
    }

    public function indexByTag(string $tag): JsonResponse
    {
        $contents = Content::ofTag($tag)->paginate();

        return fractal($contents)
            ->transformWith($this->transformer)
            ->paginateWith(new IlluminatePaginatorAdapter($contents))
            ->respond();
    }

    public function show(Content $apiContent): JsonResponse
    {
        return fractal($apiContent)
            ->transformWith($this->transformer)
            ->respond();
    }

    public function store(ContentRequest $request): JsonResponse
    {
        $content = DB::transaction(function () use ($request) {
            $content = new Content();
            $content->fill($request->validated());
            $content->saveOrFail();

            foreach ($request->getRoles() as ['user' => $user, 'role' => $role]) {
                $content->users()->attach($user, ['role' => $role]);
            }

            foreach ($request->getTags() as $tag) {
                $content->tags()->attach(Tag::findOrCreateFromString($tag), [
                    'verbatim_name' => Tag::extractVerbatimName($tag),
                ]);
            }

            return $content;
        });

        return fractal($content)
            ->transformWith($this->transformer)
            ->respond(Response::HTTP_CREATED);
    }

    public function destroy(Content $apiContent): Response
    {
        $apiContent->delete();

        return response()->noContent();
    }
}
