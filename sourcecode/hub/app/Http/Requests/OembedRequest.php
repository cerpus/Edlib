<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Oembed\OembedFormat;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class OembedRequest extends FormRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'url' => ['required', 'url'],
            'format' => ['sometimes', 'required', Rule::in(OembedFormat::values())],
        ];
    }
}
