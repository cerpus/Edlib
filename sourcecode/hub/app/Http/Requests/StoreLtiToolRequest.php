<?php

namespace App\Http\Requests;

use App\Models\LtiVersion;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreLtiToolRequest extends FormRequest
{
    /**
     * @return mixed[]
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string'],
            'creator_launch_url' => ['required', 'url'],
            'lti_version' => ['required', Rule::enum(LtiVersion::class)],
            'consumer_key' => ['string'],
            'consumer_secret' => ['required_with:consumer_key', 'string'],
        ];
    }
}
