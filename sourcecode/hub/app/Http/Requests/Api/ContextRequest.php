<?php

declare(strict_types=1);

namespace App\Http\Requests\Api;

use App\Models\Context;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ContextRequest extends FormRequest
{
    /**
     * @return array<mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'regex:/^\w+$/', Rule::unique(Context::class, 'name')],
        ];
    }
}
