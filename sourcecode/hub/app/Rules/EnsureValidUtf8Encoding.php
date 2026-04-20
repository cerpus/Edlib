<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Log;

class EnsureValidUtf8Encoding implements ValidationRule
{
    /**
     * Ensure the value is encoded in UTF-8.
     *
     * @param string $attribute The name of the attribute being validated.
     * @param mixed $value The value of the attribute to validate.
     * @param Closure $fail A callback function to report validation failure.
     * @return void
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (!mb_check_encoding($value, 'UTF-8')) {
            $fail('The :attribute contains invalid UTF-8 characters.');
        }
    }
}
