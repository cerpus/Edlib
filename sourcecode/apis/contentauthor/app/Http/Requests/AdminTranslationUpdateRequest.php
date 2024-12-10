<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;

class AdminTranslationUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Gate::allows('superadmin');
    }

    public function rules(): array
    {
        return [
            'translationFile' => [
                'required_without:translation',
                'filled',
                'file',
                'max:50',
            ],
            'translation' => [
                'required_without:translationFile',
                'filled',
                'json',
                'max:51200',
            ],
        ];
    }
}
