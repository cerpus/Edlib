<?php

namespace App\Libraries\NDLA\Importers\Handlers\APIArticle;

use App\Article;
use Cerpus\VersionClient\VersionData;
use Cerpus\VersionClient\VersionClient;

class Versioning extends BaseHandler
{
    public function process(Article $article, $jsonArticle): Article
    {
        $this->article = $article;
        $this->jsonArticle = $jsonArticle;

        $this->debug("Processing Versioning");

        if (config('feature.versioning')) {
            if (!$this->article->version_id) { // Has not been registered before
                /** @var VersionData $versionData */
                $versionData = resolve(VersionData::class);
                $versionData->setUserId($this->article->owner_id)
                    ->setExternalReference($this->article->id)
                    ->setExternalSystem(config('app.site-name'))
                    ->setExternalUrl(route('article.show', $this->article->id))
                    ->setOriginSystem('ndla.no')
                    ->setOriginId($this->jsonArticle->id)
                    ->setParentId($this->getParentVersionId())
                    ->setVersionPurpose($this->getVersionPurpose());

                /** @var VersionClient $versionClient */
                $versionClient = resolve(VersionClient::class);

                /** @var VersionData $newVersion */
                $newVersion = $versionClient->createVersion($versionData);

                $this->article->version_id = $newVersion->getId();
                $this->article->save();
                $this->debug('Versioning: Version ID: ' . $this->article->version_id . '.');
            } else {
                $this->debug('Versioning: Already versioned as ' . $this->article->version_id . '.');
            }
        } else {
            $this->debug('Versioning disabled.');
        }

        return $this->article;
    }

    protected function getParentVersionId()
    {
        return $this->article->parent_version_id;
    }

    private function getVersionPurpose()
    {
        return $this->getParentVersionId() ? VersionData::UPDATE : VersionData::CREATE;
    }
}
