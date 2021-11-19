<?php

namespace App\Libraries;

use App\Article;
use App\Game;
use App\H5PContent;
use App\Libraries\DataObjects\ResourceDataObject;
use App\QuestionSet;

class ModelRetriever
{
    public static function getModelFromGroup($group): string
    {
        switch (strtolower($group)) {
            case "article":
                return Article::class;
            case "game":
                return Game::class;
            case "questionset":
                return QuestionSet::class;
            case "h5p":
            default:
                return H5PContent::class;
        }
    }

    public static function getModelFromContentType($contentType): string
    {
        if (starts_with(strtolower($contentType), 'h5p.')) {
            return H5PContent::class;
        }

        switch (strtolower($contentType)) {
            case "article":
                return Article::class;
            case "game":
                return Game::class;
            case "questionset":
                return QuestionSet::class;
            case "h5p":
            default:
                return H5PContent::class;
        }
    }

    public static function getGroupController($groupName)
    {
        switch ($groupName) {
            case ResourceDataObject::H5P:
                return app('App\Http\Controllers\H5PController');
            case ResourceDataObject::ARTICLE:
                return app('App\Http\Controllers\ArticleController');
            case ResourceDataObject::LINK:
                return app('App\Http\Controllers\LinkController');
            case ResourceDataObject::GAME:
                return app('App\Http\Controllers\GameController');
            case ResourceDataObject::QUESTIONSET:
                return app('App\Http\Controllers\QuestionSetController');
            default:
                return null;
        }
    }
}
