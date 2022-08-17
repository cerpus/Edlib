<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class JwtRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'jwt' => [
                ['regex', '/^([A-Za-z0-9_-]+\.){2}[A-Za-z0-9_-]+$/'],
            ],
        ];
    }
}
