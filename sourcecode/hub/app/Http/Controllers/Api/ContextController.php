<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Requests\Api\ContextRequest;
use App\Models\Context;
use App\Transformers\ContextTransformer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use League\Fractal\Pagination\IlluminatePaginatorAdapter;

use function fractal;

final readonly class ContextController
{
    public function __construct(
        private ContextTransformer $contextTransformer,
    ) {}

    public function index(): JsonResponse
    {
        $contexts = Context::paginate();

        return fractal($contexts)
            ->transformWith($this->contextTransformer)
            ->paginateWith(new IlluminatePaginatorAdapter($contexts))
            ->respond();
    }

    public function show(Context $context): JsonResponse
    {
        return fractal($context)
            ->transformWith($this->contextTransformer)
            ->respond();
    }

    public function store(ContextRequest $request): JsonResponse
    {
        $context = new Context($request->validated());
        $context->save();

        return fractal($context)
            ->transformWith($this->contextTransformer)
            ->respond();
    }

    public function destroy(Context $context): Response
    {
        $context->delete();

        return response()->noContent();
    }
}
