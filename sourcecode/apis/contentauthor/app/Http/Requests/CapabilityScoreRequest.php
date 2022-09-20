<?php

namespace App\Http\Requests;

use Auth;

class CapabilityScoreRequest extends Request
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
            'score' => 'required|numeric|in:0,1'
        ];
    }

    public function messages()
    {
        return [
            'score.in' => 'Score must be 0 or 1',
        ];
    }
}
