<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Models\LtiTool;
use App\Transformers\LtiToolTransformer;
use Illuminate\Http\JsonResponse;
use League\Fractal\Pagination\IlluminatePaginatorAdapter;

use function fractal;

final readonly class LtiToolController
{
    public function __construct(private LtiToolTransformer $transformer) {}

    public function index(): JsonResponse
    {
        $tools = LtiTool::paginate();

        return fractal($tools)
            ->transformWith($this->transformer)
            ->paginateWith(new IlluminatePaginatorAdapter($tools))
            ->respond();
    }

    public function show(LtiTool $tool): JsonResponse
    {
        return fractal($tool)
            ->transformWith($this->transformer)
            ->respond();
    }
}
