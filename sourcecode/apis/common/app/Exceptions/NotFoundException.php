<?php

namespace App\Exceptions;

use Illuminate\Http\JsonResponse;
use Throwable;

class NotFoundException extends \Exception
{
    public function __construct(
        private ?string $field = null,
        int $code = 0,
        Throwable $previous = null
    )
    {
        parent::__construct($field ? "$field not found" : "Not found", $code, $previous);
    }

    public function render(): JsonResponse
    {
        return response()->json([
            'message' => $this->message,
            "parameter" => $this->field
        ], 404);
    }
}
