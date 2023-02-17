<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ApiTranslationRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'fields' => ['required', 'array'],
            'fields.*' => ['array'],
            'fields.*.path' => ['required', 'string'],
            'fields.*.value' => ['required', 'string'],
        ];
    }
}
