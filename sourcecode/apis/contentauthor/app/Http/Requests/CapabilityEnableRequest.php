<?php

namespace App\Http\Requests;

use Auth;
use Illuminate\Foundation\Http\FormRequest;

class CapabilityEnableRequest extends FormRequest
{
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
        return [
            'enabled' => 'required|numeric|in:0,1',
        ];
    }

    public function messages()
    {
        return [
            'enabled.in' => 'Enabled must be 0 or 1',
        ];
    }
}
