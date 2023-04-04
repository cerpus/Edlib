<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Configuration\Locales;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class SavePreferencesRequest extends FormRequest
{
    /**
     * @return mixed[]
     */
    public function rules(Locales $locales): array
    {
        return [
            'locale' => ['required', Rule::in($locales->all())],
        ];
    }
}
