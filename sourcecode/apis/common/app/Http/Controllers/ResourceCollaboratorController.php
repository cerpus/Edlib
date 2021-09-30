<?php

namespace App\Http\Controllers;

use App\Apis\ResourceApiService;
use App\Http\Requests\SetResourceCollaboratorsRequest;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

final class ResourceCollaboratorController extends Controller
{
    /**
     * @throws Throwable
     */
    public function set(SetResourceCollaboratorsRequest $request): Response
    {
        $data = $request->validated();

        App::call(fn(ResourceApiService $resourceApiService) => $resourceApiService->setResourceCollaborators(
            Auth::user()->id,
            $data["context"],
            $data["tenantIds"],
            $data["resourceIds"] ?? null,
            $data["externalResources"] ?? null
        ));

        return new Response('', Response::HTTP_CREATED);
    }
}
