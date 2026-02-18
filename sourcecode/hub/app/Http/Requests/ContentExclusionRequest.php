<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ContentExclusionRequest extends FormRequest
{
    /**
     * @return array<string, string>
     */
    public function rules(): array
    {
        return [
            'content_ids' => 'sometimes|array',
            'content_ids.*' => 'ulid|exists:contents,id',
            'exclude_from' => 'required|string',
            'user_id' => 'sometimes|string|nullable',
        ];
    }
}
