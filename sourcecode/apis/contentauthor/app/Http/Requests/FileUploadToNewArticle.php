<?php

namespace App\Http\Requests;

use App\ACL\ArticleAccess;

class FileUploadToNewArticle extends Request
{
    use ArticleAccess;

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return $this->canCreate();
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
