<?php

declare(strict_types=1);

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Override;

use function is_string;
use function preg_match;

class Iso639_3Language implements ValidationRule
{
    #[Override]
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (!is_string($value) || !preg_match('/\A[a-z]{3}\z/', $value)) {
            $fail(':attribute must be ISO 639-3 language string');
        }
    }
}
