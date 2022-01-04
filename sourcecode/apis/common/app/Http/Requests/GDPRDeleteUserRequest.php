<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class GDPRDeleteUserRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'requestId' => ['required', 'string'],
            'userId' => ['required', 'string'],
            'emails' => ['array'],
        ];
    }
}
