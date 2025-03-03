<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ContentStatusRequest extends FormRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            // TODO: check specific role for content
            'shared' => ['boolean'],
        ];
    }

    public function contentIsShared(): bool
    {
        return (bool) $this->validated('shared', false);
    }
}
