<?php

namespace App\Http\Requests;

class FileUploadToNewArticle extends Request
{
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
