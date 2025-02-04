<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class UpdateUserRequest extends FormRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $userId = $this->user()?->id;
        return [
            'name' => ['required', 'string', 'max:100'],
            'password' => ['sometimes', 'nullable', 'confirmed', Password::min(8)],
            'email' => [
                'sometimes',
                'nullable',
                'email',
                'unique:users,email,' . $userId,
            ],
        ];
    }
}
