<?php

namespace App\Http\Requests;

use Auth;
use App\Traits\Locale;
use Illuminate\Foundation\Http\FormRequest;

class CapabilityDescriptionPostRequest extends FormRequest
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
            'title' => 'required|min:1|max:255',
            'description' => 'sometimes|min:1|max:255',
        ];
    }

    public function messages()
    {
        return [
            'locale.in' => "Locale must be one of " . $this->getSupportedLocalesAsString(),
        ];
    }
}
