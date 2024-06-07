<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Requests\Api\ContentRequest;
use App\Models\Content;
use App\Transformers\ContentTransformer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use League\Fractal\Pagination\IlluminatePaginatorAdapter;

use function fractal;
use function response;

final readonly class ContentController
{
    public function __construct(private ContentTransformer $transformer)
    {
    }

    public function index(): JsonResponse
    {
        $contents = Content::paginate();

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
        $content = new Content();
        $content->forceFill($request->validated());
        $content->saveOrFail();

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
