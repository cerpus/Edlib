<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class GrantAdminRequest extends FormRequest
{
    /**
     * @return array<mixed>
     */
    public function rules(): array
    {
        return [
            'email' => ['required', 'email', Rule::exists(User::class, 'email')],
        ];
    }
}
