<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Enums\LtiToolEditMode;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreLtiToolRequest extends FormRequest
{
    public function prepareForValidation(): void
    {
        $parameters = $this->getInputSource();

        if (!$parameters->get('slug')) {
            $parameters->remove('slug');
        }
    }

    /**
     * @return mixed[]
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string'],
            'creator_launch_url' => ['required', 'url'],
            'consumer_key' => ['required', 'string'],
            'consumer_secret' => ['required', 'string'],
            'edit_mode' => ['required', Rule::enum(LtiToolEditMode::class)],
            'send_name' => ['boolean'],
            'send_email' => ['boolean'],
            'slug' => ['sometimes', 'string', 'max:50', 'regex:/^[a-z0-9-_]+$/'],
        ];
    }
}
