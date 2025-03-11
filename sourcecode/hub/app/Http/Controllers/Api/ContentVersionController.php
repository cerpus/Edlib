<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Requests\Api\ContentVersionRequest;
use App\Models\Content;
use App\Models\ContentVersion;
use App\Models\Tag;
use App\Transformers\ContentVersionTransformer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

use function fractal;
use function response;

final readonly class ContentVersionController
{
    public function __construct(private ContentVersionTransformer $transformer) {}

    public function show(Content $apiContent, ContentVersion $version): JsonResponse
    {
        return fractal($version)
            ->transformWith($this->transformer)
            ->respond();
    }

    public function store(
        Content $apiContent,
        ContentVersionRequest $request,
    ): JsonResponse {
        $version = DB::transaction(function () use ($apiContent, $request) {
            $version = new ContentVersion();
            $version->fill($request->validated());
            $apiContent->versions()->save($version);

            foreach ($request->getTags() as $tag) {
                $version->tags()->attach(Tag::findOrCreateFromString($tag), [
                    'verbatim_name' => Tag::extractVerbatimName($tag),
                ]);
            }

            return $version;
        });

        return fractal($version)
            ->transformWith($this->transformer)
            ->respond(Response::HTTP_CREATED);
    }

    public function destroy(Content $apiContent, ContentVersion $version): Response
    {
        $version->delete();

        return response()->noContent();
    }
}
