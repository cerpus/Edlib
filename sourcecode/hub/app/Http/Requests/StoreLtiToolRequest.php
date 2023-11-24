<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Models\LtiToolEditMode;
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
            'edit_mode' => ['required', Rule::enum(LtiToolEditMode::class)],
            'send_name' => ['boolean'],
            'send_email' => ['boolean'],
            'proxy_launch' => ['boolean'],
        ];
    }
}
