<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class IndexContentRequest extends FormRequest
{
    /**
     * @return mixed[]
     */
    public function rules(): array
    {
        return [
            'q' => ['sometimes', 'string'],
        ];
    }
}
