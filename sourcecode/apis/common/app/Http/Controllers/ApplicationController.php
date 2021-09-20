<?php

namespace App\Http\Controllers;

use App\Http\Requests\ApplicationRequest;
use App\Models\Application;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

final class ApplicationController extends Controller
{
    public function list(): JsonResponse
    {
        return new JsonResponse(Application::all()->toArray());
    }

    public function create(ApplicationRequest $request): JsonResponse
    {
        $application = Application::create($request->validated());

        return new JsonResponse($application->toArray(), Response::HTTP_CREATED);
    }
}
