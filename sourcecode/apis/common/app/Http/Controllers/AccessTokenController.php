<?php

namespace App\Http\Controllers;

use App\Http\Requests\AccessTokenRequest;
use App\Models\AccessToken;
use App\Models\Application;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

final class AccessTokenController extends Controller
{
    public function listByApplication(Application $application): JsonResponse
    {
        return new JsonResponse($application->accessTokens()->get()->toArray());
    }

    public function create(Application $application, AccessTokenRequest $request): JsonResponse
    {
        $data = $request->validated();
        $accessToken = $application->accessTokens()->create($data);

        return new JsonResponse(
            $accessToken->makeVisible('token')->toArray(),
            Response::HTTP_CREATED,
        );
    }

    public function delete(Application $application, AccessToken $accessToken): Response
    {
        $application->accessTokens()->find($accessToken)->firstOrFail()->delete();

        return new Response('', Response::HTTP_NO_CONTENT);
    }
}
