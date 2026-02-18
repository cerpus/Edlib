<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

final class ApiController extends Controller
{
    public function __invoke(): JsonResponse
    {
        $user = $this->getUser();

        return new JsonResponse([
            'it worked' => true,
            'user' => $user,
        ]);
    }
}
