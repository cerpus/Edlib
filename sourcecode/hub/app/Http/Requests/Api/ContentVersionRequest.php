<?php

declare(strict_types=1);

namespace App\Http\Requests\Api;

use App\Models\LtiTool;
use App\Models\User;
use App\Rules\Iso639_3Language;
use Illuminate\Contracts\Auth\Access\Gate;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ContentVersionRequest extends FormRequest
{
    protected function passedValidation(): void
    {
        $this->mergeIfMissing([
            'edited_by' => $this->user(),
            'tags' => [],
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(Gate $gate, Iso639_3Language $languageRule): array
    {
        return [
            'title' => ['required', 'string'],
            'lti_tool_id' => ['required', Rule::exists(LtiTool::class, 'id')],
            'lti_launch_url' => ['required', 'url'],
            'language_iso_639_3' => ['sometimes', $languageRule],
            'license' => ['sometimes', 'string'],
            'published' => ['sometimes', 'boolean'],

            'tags.*' => ['string'],

            'min_score' => ['sometimes', 'numeric', 'required_with:max_score', 'lte:max_score'],
            'max_score' => ['sometimes', 'numeric', 'required_with:min_score'],

            'created_at' => [
                'sometimes',
                Rule::prohibitedIf(fn() => $gate->denies('admin')),
                'date',
            ],

            'edited_by' => [
                'sometimes',
                Rule::prohibitedIf(fn() => $gate->denies('admin')),
                Rule::exists(User::class, 'id'),
            ],
        ];
    }

    /**
     * @return string[]
     */
    public function getTags(): array
    {
        return $this->validated('tags', []);
    }
}
