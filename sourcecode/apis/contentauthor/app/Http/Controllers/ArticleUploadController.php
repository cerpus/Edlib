<?php

namespace App\Http\Controllers;

use Session;
use App\File;
use App\Article;
use App\Http\Requests\FileUploadToArticle;
use App\Http\Requests\FileUploadToNewArticle;

class ArticleUploadController extends Controller
{
    public function uploadToExistingArticle(FileUploadToArticle $request, $id)
    {
        try {
            $article = Article::findOrFail($id);
            $newFile = File::addUploadedFileToArticle($request->file('upload'), $article);
        } catch (\Exception $e) {
            return ['uploaded' => 0, 'error' => ['message' => trans('article.could-not-upload-file')]];
        }

        return ['uploaded' => 1, 'fileName' => $newFile->name, 'url' => $newFile->generatePath()];
    }

    public function uploadToNewArticle(FileUploadToNewArticle $request)
    {
        try {
            $newFile = File::moveUploadedFileToTmp($request->file('upload'));
            Session::push(Article::TMP_UPLOAD_SESSION_KEY, $newFile);
        } catch (\Exception $e) {
            return ['uploaded' => 0, 'error' => ['message' => trans('article.could-not-upload-file')]];
        }

        return ['uploaded' => 1, 'fileName' => $newFile->name, 'url' => $newFile->generateTempPath()];
    }
}
