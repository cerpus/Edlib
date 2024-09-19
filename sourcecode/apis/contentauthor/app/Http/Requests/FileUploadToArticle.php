<?php

namespace App\Http\Requests;

class FileUploadToArticle extends Request
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // TODO: check intent for LTI launch
        return false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'upload' => 'required|mimes:jpeg,gif,png',
        ];
    }
}
