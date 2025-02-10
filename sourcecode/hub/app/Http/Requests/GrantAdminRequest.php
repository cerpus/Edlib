<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Rules\VerifiedUserEmail;
use Illuminate\Foundation\Http\FormRequest;

class GrantAdminRequest extends FormRequest
{
    /**
     * @return array<mixed>
     */
    public function rules(VerifiedUserEmail $verifiedUserEmail): array
    {
        return [
            'email' => ['required', 'email', $verifiedUserEmail],
        ];
    }
}
