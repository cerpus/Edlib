<?php

namespace App\Libraries\NDLA\Importers\Handlers\Helpers;


trait ArticleHash
{
    protected function generateChecksumHash($apiArticle)
    {
        try {
            $hashElements = [
                $apiArticle->id,
                $apiArticle->title ?? '',
                $apiArticle->updated ?? '',
                $apiArticle->updatedBy ?? '',
                $apiArticle->content->content ?? '',
                $apiArticle->content->language ?? 'nb',
            ];

            return sha1(json_encode($hashElements));
        } catch (\Exception $e) {
            return null;
        }
    }
}
