<?php

namespace App\Http\Controllers;

use App\Apis\ContentAuthorService;
use App\Apis\LtiApiService;
use App\Apis\ResourceApiService;
use App\Http\Requests\H5pGenerateFromQARequest;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

final class H5PController extends Controller
{
    public function __construct(
        private ContentAuthorService $contentAuthorService,
        private ResourceApiService $resourceApiService,
        private LtiApiService $ltiApiService,
    )
    {}

    /**
     * @throws Throwable
     */
    public function generateFromQA(H5pGenerateFromQARequest $request): Response
    {
        $data = $request->validated();

        $h5pInfo = $this->contentAuthorService->generateH5pFromQA($data)->wait();
        $resourceVersion = $this->resourceApiService->ensureResourceExists('contentauthor', $h5pInfo['id']);
        $resource = $this->resourceApiService->getResource($resourceVersion->resourceId);
        $ltiUsage = $this->ltiApiService->createUsage($resourceVersion->resourceId, $resourceVersion->id);

        $resource->setVersion($resourceVersion);

        return new JsonResponse([
            "resource" => $resource,
            "usageId" => $ltiUsage->id
        ], Response::HTTP_CREATED);
    }
}
