<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;

class AdminTranslationContentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Gate::allows('superadmin');
    }

    public function rules(): array
    {
        return [
            'libraryId' => [
                'required',
                'int',
            ],
            'locale' => [
                'required',
                'string',
            ],
            'processed' => [
                'filled',
                'array',
            ],
            'processed.*' => [
                'required',
                'json',
            ],
        ];
    }
}
