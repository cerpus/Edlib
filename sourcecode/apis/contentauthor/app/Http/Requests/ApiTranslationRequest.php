<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ApiTranslationRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            // TODO: check supported languages
            'from_lang' => ['required', 'string'],
            'to_lang' => ['required', 'string'],
            'fields' => ['required', 'array'],
            'fields.*' => ['array'],
            'fields.*.path' => ['required', 'string'],
            'fields.*.value' => ['required', 'string'],
        ];
    }
}
