<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Configuration\Locales;
use App\Configuration\Themes;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class SavePreferencesRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $parameters = $this->getInputSource();

        if (!$parameters->has('debug_mode')) {
            $parameters->set('debug_mode', false);
        }

        if (!$parameters->get('theme')) {
            $parameters->set('theme', null);
        }
    }

    /**
     * @return mixed[]
     */
    public function rules(Locales $locales, Themes $themes): array
    {
        return [
            'locale' => [
                Rule::excludeIf(fn() => $this->session()->has('lti.launch_presentation_locale')),
                'required',
                Rule::in($locales->all()),
            ],
            'theme' => ['sometimes', 'nullable', Rule::in($themes->all())],
            'debug_mode' => ['required', 'boolean'],
        ];
    }
}
