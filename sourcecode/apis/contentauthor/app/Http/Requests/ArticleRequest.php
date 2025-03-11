<?php

namespace App\Http\Requests;

use App\Rules\LicenseContent;
use App\Rules\shareContent;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

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
            'share' => ['sometimes', new shareContent()],
            'license' => [Rule::requiredIf($this->input('share') === 'share'), 'string', app(LicenseContent::class)],
        ];
    }
}
