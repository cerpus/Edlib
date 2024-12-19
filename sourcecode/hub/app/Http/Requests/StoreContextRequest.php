<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Models\Context;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreContextRequest extends FormRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'regex:/^\w+$/', Rule::unique(Context::class, 'name')],
        ];
    }
}
