<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Requests\Api\UserRequest;
use App\Models\User;
use App\Transformers\UserTransformer;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

use function fractal;

final readonly class UserController
{
    public function __construct(private UserTransformer $transformer) {}

    public function show(User $user): JsonResponse
    {
        return fractal($user)
            ->transformWith($this->transformer)
            ->respond();
    }

    public function create(UserRequest $request): JsonResponse
    {
        $user = User::forceCreate($request->validated());

        return fractal($user)
            ->transformWith($this->transformer)
            ->respond(Response::HTTP_CREATED);
    }
}
