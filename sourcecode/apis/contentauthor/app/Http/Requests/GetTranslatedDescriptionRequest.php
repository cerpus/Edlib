<?php

namespace App\Http\Requests;

use Auth;
use App\Traits\Locale;

class GetTranslatedDescriptionRequest extends Request
{
    use Locale;

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return Auth::check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $locales = $this->getSupportedLocalesAsString();
        return [
            'locale' => 'required|in:' . $locales,
        ];
    }

    public function messages()
    {
        return [
            'locale.in' => "Locale must be one of " . $this->getSupportedLocalesAsString(),
        ];
    }
}
