<?php

namespace App\Http\Controllers;

use App\Apis\ContentAuthorService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

final class ContentAuthorController extends Controller
{
    public function __construct(
        private ContentAuthorService $contentAuthorService,
    )
    {}

    /**
     * @throws Throwable
     */
    public function questionAndAnswers(Request $request): Response
    {
        $response = $this->contentAuthorService->getQuestionAndAnswers($request->all())->wait();
        return new JsonResponse($response);
    }
}
