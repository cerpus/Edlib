<?php

declare(strict_types=1);

namespace App\Http\Requests\Api;

use App\Configuration\Locales;
use App\Configuration\Themes;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

use function is_string;
use function strtolower;

class UserRequest extends FormRequest
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
    public function rules(Locales $locales, Themes $themes): array
    {
        return [
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', Rule::unique(User::class, 'email')],
            'admin' => ['sometimes', 'boolean'],
            'debug_mode' => ['sometimes', 'boolean'],
            'theme' => ['sometimes', Rule::in($themes->all())],
            'locale' => ['sometimes', Rule::in($locales->all())],
            'created_at' => ['sometimes', 'date'],
        ];
    }
}
