<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ContentInfoRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'content_url' => 'required|url',
        ];
    }
}
