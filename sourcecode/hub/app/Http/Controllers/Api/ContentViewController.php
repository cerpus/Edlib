<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Models\Content;
use App\Transformers\ContentViewTransformer;
use Illuminate\Http\JsonResponse;
use League\Fractal\Pagination\IlluminatePaginatorAdapter;

final readonly class ContentViewController
{
    public function __construct(private ContentViewTransformer $transformer) {}

    public function index(Content $apiContent): JsonResponse
    {
        $views = $apiContent->views()->paginate();

        return fractal($views)
            ->transformWith($this->transformer)
            ->paginateWith(new IlluminatePaginatorAdapter($views))
            ->respond();
    }
}
