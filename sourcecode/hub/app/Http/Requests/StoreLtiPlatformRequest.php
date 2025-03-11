<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Models\LtiPlatform;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreLtiPlatformRequest extends FormRequest
{
    /**
     * @return array<mixed>
     */
    public function rules(): array
    {
        return [
            'name' => [
                'required',
                'max:100',
                Rule::unique(LtiPlatform::class, 'name'),
            ],
            'enable_sso' => ['boolean'],
            'authorizes_edit' => ['boolean'],
        ];
    }
}
