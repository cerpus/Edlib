<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SavedFilterRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'name' => ['required', 'string'],
            'choices' => ['array'],
            'choices.*.group' => ['required_with:choices', 'string'],
            'choices.*.value' => ['required_with:choices', 'string'],
        ];
    }
}
