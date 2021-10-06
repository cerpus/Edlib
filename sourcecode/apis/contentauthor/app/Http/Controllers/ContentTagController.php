<?php

namespace App\Http\Controllers;

use Log;
use App\Game;
use App\Link;
use App\Article;
use App\H5PContent;

class ContentTagController extends Controller
{
    /**
     * Return tags for H5P content
     * @param H5PContent $h5p
     */
    public function fetchH5PTags(H5PContent $h5p)
    {
        return response()->json($this->fetchTags($h5p));
    }

    /**
     * Return tags for Article content
     * @param  Article $article
     */
    public function fetchArticleTags(Article $article)
    {
        return response()->json($this->fetchTags($article));
    }

    /**
     * Return tags for Game content
     * @param  Game $game
     */
    public function fetchGameTags(Game $game)
    {
        return response()->json($this->fetchTags($game));
    }

    /**
     * Return tags for Link content
     * @param  Link $link
     * @return array
     */
    public function fetchLinkTags(Link $link)
    {
        return response()->json($this->fetchTags($link));
    }

    protected function fetchTags($content): array
    {
        $tags = [];

        try {
            $tags = $content->getMetaTagsAsArray();
        } catch (\Throwable $exception) {
            Log::error(__METHOD__ . ': error fetching metadata (' . $exception->getCode() . ') ' . $exception->getMessage());
        }

        return $tags;
    }
}
