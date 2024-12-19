<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Enums\ContentRole;
use App\Models\Context;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AddContextToContentRequest extends FormRequest
{
    /**
     * @return array<mixed>
     */
    public function rules(): array
    {
        return [
            'context' => ['required', 'string', Rule::exists(Context::class, 'id')],
            'role' => ['required', Rule::enum(ContentRole::class)],
        ];
    }
}
