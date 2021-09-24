<?php

namespace App\Http\Controllers;

use App\Http\Requests\ApplicationRequest;
use App\Models\Application;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

final class ApplicationController extends Controller
{
    /**
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function list(): JsonResponse
    {
        $this->authorize('admin');

        return new JsonResponse(Application::all()->toArray());
    }

    public function get(Application $application): JsonResponse
    {
        $this->authorize('admin');

        return new JsonResponse($application->toArray());
    }

    public function create(ApplicationRequest $request): JsonResponse
    {
        $this->authorize('admin');

        $application = Application::create($request->validated());

        return new JsonResponse($application->toArray(), Response::HTTP_CREATED);
    }
}
