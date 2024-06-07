<?php

declare(strict_types=1);

namespace App\Http\Requests\Api;

use Illuminate\Contracts\Auth\Access\Gate;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class ContentRequest extends FormRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(Gate $gate): array
    {
        return [
            'shared' => ['sometimes', 'boolean'],

            'created_at' => [
                Rule::prohibitedIf($gate->denies('admin')),
                'sometimes',
                'date',
            ],
        ];
    }
}
