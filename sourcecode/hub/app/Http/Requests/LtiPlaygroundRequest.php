<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

use function parse_str;

class LtiPlaygroundRequest extends FormRequest
{
    /**
     * @return array<mixed>
     */
    public function rules(): array
    {
        if ($this->isMethod('GET')) {
            return [];
        }

        return [
            'launch_url' => ['required', 'url'],
            'key' => ['required', 'string'],
            'secret' => ['required', 'string'],
            'parameters' => ['sometimes', 'string'],
            'time' => ['sometimes', 'date'],
        ];
    }

    /**
     * @return array<array-key, string>
     */
    public function getParameters(): array
    {
        parse_str($this->validated('parameters', ''), $parameters);

        $parameters += [
            'lti_message_type' => 'basic-lti-launch-request',
            'lti_version' => 'LTI-1p0',
        ];

        // Future improvement: would be better if array params were rejected in
        // the validation stage.
        return array_filter($parameters, is_string(...));
    }
}
