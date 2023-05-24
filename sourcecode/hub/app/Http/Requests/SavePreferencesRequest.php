<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Configuration\Locales;
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
    }

    /**
     * @return mixed[]
     */
    public function rules(Locales $locales): array
    {
        return [
            'locale' => ['required', Rule::in($locales->all())],
            'debug_mode' => ['required', 'boolean'],
        ];
    }
}
