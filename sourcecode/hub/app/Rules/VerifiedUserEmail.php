<?php

declare(strict_types=1);

namespace App\Rules;

use App\Models\User;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * Ensure an email belongs to a user with a verified email address.
 */
class VerifiedUserEmail implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $user = User::where('email', $value)->first();

        if ($user === null) {
            $fail('No user with that email address');

            return;
        }

        if (!$user->email_verified) {
            $fail('User does not have a verified email address');
        }
    }
}
