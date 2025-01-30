<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

use function is_string;
use function strtolower;

class UpdateUserRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $email = $this->input('email');

        if (is_string($email)) {
            $this->merge([
                'email' => strtolower($email),
            ]);
        }
    }

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
