<?php

namespace App\Listeners\Article;

use App\Events\ArticleWasSaved;
use App\Article;
use App\Events\Event;
use App\Libraries\Versioning\VersionableObject;
use App\Listeners\AbstractHandleVersioning;
use Cerpus\VersionClient\exception\LinearVersioningException;
use Cerpus\VersionClient\VersionData;
use Cerpus\VersionClient\VersionClient;
use Illuminate\Support\Facades\Log;

class HandleVersioning extends AbstractHandleVersioning
{
    protected $article;

    public function __construct(VersionClient $versionClient) {
        parent::__construct($versionClient);
    }

    /**
     * Handle the event.
     *
     * @param  ArticleWasSaved $event
     * @return void
     */
    public function handle(Event $event)
    {
        $this->article = $event->article->fresh();

        $this->handleSave($this->article, $event->reason);
    }

    public function getParentVersionId()
    {
        if (is_null($this->article->parent_id)) {
            return null;
        }

        if (!is_null($this->article->parent_version_id)) {
            return $this->article->parent_version_id;
        }

        $parentArticle = Article::find($this->article->parent_id);

        if (is_null($parentArticle->version_id)) { // Create origin version
            $versionData = new VersionData();
            $versionData->setUserId($parentArticle->owner_id)
                ->setExternalReference($parentArticle->id)
                ->setExternalSystem(config('app.site-name'))
                ->setExternalUrl(route('article.show', $parentArticle->id))
                ->setVersionPurpose(VersionData::CREATE);

            $version = $this->versionClient->createVersion($versionData);

            if (!$version) {
                Log::error('Versioning failed: ' . $this->versionClient->getErrorCode() . ': ' . $this->versionClient->getMessage());
                //Maybe do something more constructive...add to queue to try again?
            } else {
                $parentArticle->version_id = $version->getId();
                $parentArticle->save();
            }

            return $parentArticle->version_id;
        }

        return null;
    }

    protected function getExternalUrl(VersionableObject $object)
    {
        return route('article.show', $object->getId());
    }
}
