<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreLtiToolExtraRequest extends FormRequest
{
    public function prepareForValidation(): void
    {
        $parameters = $this->getInputSource();

        if (!$parameters->get('slug')) {
            $parameters->remove('slug');
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:100'],
            'lti_launch_url' => ['required', 'url'],
            'admin' => ['sometimes', 'boolean'],
            'slug' => ['sometimes', 'string', 'max:50', 'regex:/^[a-z0-9-_]+$/'],
        ];
    }
}
