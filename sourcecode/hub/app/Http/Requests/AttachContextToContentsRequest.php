<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Models\Context;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AttachContextToContentsRequest extends FormRequest
{
    /**
     * @return array<mixed>
     */
    public function rules(): array
    {
        return [
            'context' => ['required', Rule::exists(Context::class, 'id')],
        ];
    }

    public function getContext(): Context
    {
        return Context::where('id', $this->validated('context'))->firstOrFail();
    }
}
