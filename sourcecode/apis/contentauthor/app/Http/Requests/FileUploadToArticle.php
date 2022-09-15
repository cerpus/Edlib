<?php

namespace App\Http\Requests;

use App\Article;
use App\ACL\ArticleAccess;

class FileUploadToArticle extends Request
{
    use ArticleAccess;

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        $article = Article::findOrFail($this->route('id'));
        return $this->canUpdateArticle($article);
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
