<?php

namespace App\Http\Requests;

use App\Rules\LicenseContent;
use Illuminate\Foundation\Http\FormRequest;

class ArticleRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'title' => 'required|min:1|max:255',
            'content' => 'required|filled',
            'origin' => 'nullable|min:1,max:1000',
            'originators' => 'nullable|array',
            'originators.*.name' => 'required|min:1|max:1000',
            'originators.*.role' => 'required|in:Source,Supplier,Writer',
            'isShared' => ['sometimes', 'boolean'],
            'license' => ['required_if_accepted:isShared', 'string', app(LicenseContent::class)],
        ];
    }
}
