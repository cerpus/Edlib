<?php

namespace App\Exceptions;

use Illuminate\Http\JsonResponse;
use Throwable;

class NotFoundException extends \Exception
{
    private $field;

    public function __construct(
        ?string $field = null,
        int $code = 0,
        Throwable $previous = null
    ) {
        parent::__construct($field ? "$field not found" : "Not found", $code, $previous);
        $this->field = $field;
    }

    public function render(): JsonResponse
    {
        return response()->json([
            'message' => $this->message,
            "parameter" => $this->field
        ], 404);
    }
}
