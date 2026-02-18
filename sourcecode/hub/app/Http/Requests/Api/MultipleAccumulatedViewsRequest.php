<?php

declare(strict_types=1);

namespace App\Http\Requests\Api;

use App\Enums\ContentViewSource;
use App\Models\LtiPlatform;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class MultipleAccumulatedViewsRequest extends FormRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'views.*.view_count' => ['required', 'integer', 'min:1'],
            'views.*.source' => ['required', Rule::enum(ContentViewSource::class)],
            'views.*.lti_platform_id' => [
                'missing_unless:source,lti_platform',
                'required_if:source,lti_platform',
                Rule::exists(LtiPlatform::class),
            ],
            'views.*.date' => ['required', Rule::date()->format('Y-m-d')],
            'views.*.hour' => ['required', 'integer', 'min:0', 'max:23'],
        ];
    }
}
