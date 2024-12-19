<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Enums\LtiToolEditMode;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class UpdateLtiToolRequest extends FormRequest
{
    public function prepareForValidation(): void
    {
        $parameters = $this->getInputSource();

        if ($parameters->get('consumer_secret', '') === '') {
            $parameters->remove('consumer_secret');
        }

        if (!$parameters->get('slug')) {
            $parameters->remove('slug');
        }

        $parameters->set('send_name', $parameters->getBoolean('send_name', false));
        $parameters->set('send_email', $parameters->getBoolean('send_email', false));
        $parameters->set('proxy_launch', $parameters->getBoolean('proxy_launch', false));
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
            'consumer_secret' => ['sometimes', 'string'],
            'edit_mode' => ['required', Rule::enum(LtiToolEditMode::class)],
            'send_name' => ['boolean'],
            'send_email' => ['boolean'],
            'proxy_launch' => ['boolean'],
            'slug' => ['sometimes', 'string', 'max:50', 'regex:/^[a-z0-9-_]+$/'],
        ];
    }
}
