<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateAuthMigrationRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'userIds' => ['required', 'array'],
            'userIds.*.from' => ['required', 'string'],
            'userIds.*.to' => ['required', 'string'],
        ];
    }
}
