<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class H5pGenerateFromQARequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'authId' => ['required', 'string'],
            'license' => ['required', 'string'],
            'sharing' => ['required', 'bool'],
            'title' => ['required', 'string'],
            'published' => ['bool'],
            'questions' => ['required', 'array'],
            'questions.*.type' => ['required', 'string'],
            'questions.*.text' => ['required', 'string'],
            'questions.*.answers' => ['required', 'array'],
            'questions.*.answers.*.text' => ['required', 'string'],
            'questions.*.answers.*.correct' => ['required', 'bool'],
        ];
    }
}
