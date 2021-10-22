<?php

namespace App\Exceptions;

use Illuminate\Http\JsonResponse;
use Throwable;

class UnauthorizedException extends \Exception
{
    public function __construct()
    {
        parent::__construct("You do not have access to this resource");
    }

    public function render(): JsonResponse
    {
        return response()->json([
            'message' => $this->message,
        ], 401);
    }
}
