<?php

namespace App\Libraries;

use App\Article;
use App\Game;
use App\H5PContent;
use App\Http\Controllers\ArticleController;
use App\Http\Controllers\GameController;
use App\Http\Controllers\H5PController;
use App\Http\Controllers\LinkController;
use App\Http\Controllers\QuestionSetController;
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
                return app(H5PController::class);
            case ResourceDataObject::ARTICLE:
                return app(ArticleController::class);
            case ResourceDataObject::LINK:
                return app(LinkController::class);
            case ResourceDataObject::GAME:
                return app(GameController::class);
            case ResourceDataObject::QUESTIONSET:
                return app(QuestionSetController::class);
            default:
                return null;
        }
    }
}
