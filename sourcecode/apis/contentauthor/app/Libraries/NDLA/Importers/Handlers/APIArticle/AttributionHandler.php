<?php

namespace App\Libraries\NDLA\Importers\Handlers\APIArticle;

use App\Article;
use App\Libraries\DataObjects\Attribution;
use App\Libraries\NDLA\Importers\Handlers\Helpers\LicenseHelper;

class AttributionHandler extends BaseHandler
{
    use LicenseHelper;

    public function process(Article $article, $jsonArticle): Article
    {
        $this->article = $article;
        $this->jsonArticle = $jsonArticle;

        $this->debug("Processing Attributions");

        $attributionUrl = $this->makeAttributionUri($this->getOldArticleId($this->jsonArticle->oldNdlaUrl ?? ''));

        /** @var Attribution $attribution */
        $attribution = new Attribution();

        $attribution->setOrigin($this->handleOrigin($this->jsonArticle));

        $copyright = $this->jsonArticle->copyright ?? (object)[];

        foreach ($copyright->creators ?? [] as $creator) {
            $attribution->addOriginator($creator->name, $creator->type);
        }

        foreach (($copyright->rightsholders ?? []) as $rightsholder) {
            $attribution->addOriginator($rightsholder->name, $rightsholder->type);
        }

        if ($attributionUrl) {
            $attribution->addOriginator($attributionUrl, 'Source');
        }

        $this->article->setAttribution($attribution);

        $this->debug('Generated attribution.');

        return $this->article;
    }

    protected function makeAttributionUri($oldArticleId)
    {
        return $this->articleApiClient->fetchEffectiveUri($oldArticleId);
    }

    protected function handleOrigin($article)
    {
        $origin = 'https://ndla.no';

        if ($article->copyright->origin ?? null) {
            $origin = $article->copyright->origin;
        }

        return $origin;
    }

    protected function getOldArticleId($oldArticleUri)
    {
        $parts = parse_url($oldArticleUri);
        $path = $parts['path'];

        $id = last(explode('/', $path));

        return $id;
    }
}
