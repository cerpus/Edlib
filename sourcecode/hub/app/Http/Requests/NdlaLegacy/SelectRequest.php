<?php

declare(strict_types=1);

namespace App\Http\Requests\NdlaLegacy;

use Illuminate\Contracts\Encryption\Encrypter;
use Illuminate\Foundation\Http\FormRequest;

class SelectRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $encrypter = $this->container->get(Encrypter::class);

        $this->merge([
            'user' => $encrypter->decrypt($this->input('user')),
        ]);
    }

    /**
     * @return array<mixed>
     */
    public function rules(): array
    {
        return [
            'user.name' => ['required', 'string'],
            'user.email' => ['required', 'email'],
            'admin' => ['sometimes', 'boolean'],
            'locale' => ['sometimes', 'string'],
            'deep_link' => ['sometimes', 'boolean'],
        ];
    }
}
