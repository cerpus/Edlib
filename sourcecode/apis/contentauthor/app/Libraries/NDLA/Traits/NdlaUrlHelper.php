<?php


namespace App\Libraries\NDLA\Traits;


use App\NdlaArticleId;
use App\Libraries\NDLA\API\ArticleApiClient;

trait NdlaUrlHelper
{
    protected function fetchNewNdlaUrl($m)
    {
        $newArticleUrl = null;
        $apiClient = app(ArticleApiClient::class);

        $apiArticle = NdlaArticleId::find($m->ndla_id ?? $m->node_id);

        $ndlaId = $this->getOldArticleId($apiArticle->json->oldNdlaUrl ?? '#');

        if ($ndlaId !== '#') {
            $newArticleUrl = $apiClient->fetchEffectiveUri($ndlaId);
        }

        return $newArticleUrl;
    }

    protected function getOldArticleId($oldArticleUri)
    {
        $parts = parse_url($oldArticleUri);
        $path = $parts['path'] ?? '';

        $id = last(explode('/', $path));

        return $id;
    }
}
