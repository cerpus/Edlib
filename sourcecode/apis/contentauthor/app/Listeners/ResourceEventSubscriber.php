<?php

namespace App\Listeners;

use App\Events\ResourceSaved;
use App\Libraries\DataObjects\ResourceDataObject;

class ResourceEventSubscriber
{
    static $producer;

    public function subscribe($events)
    {
        $events->listen('App\Events\ResourceSaved', 'App\Listeners\ResourceEventSubscriber@onResourceSaved');
    }

    public function onArticleSaved($event)
    {
        $event = new ResourceSaved(
            new ResourceDataObject($event->article->id, $event->article->title, $event->article->wasRecentlyCreated === true ? ResourceSaved::CREATE : ResourceSaved::UPDATE, ResourceDataObject::ARTICLE),
            $event->article->getEdlibDataObject()
        );
        return $this->onResourceSaved($event);
    }

    public function onLinkSaved($event)
    {
        $event = new ResourceSaved(
            new ResourceDataObject($event->link->id, $event->link->title, $event->link->wasRecentlyCreated === true ? ResourceSaved::CREATE : ResourceSaved::UPDATE, ResourceDataObject::LINK),
            $event->link->getEdlibDataObject()
        );
        return $this->onResourceSaved($event);
    }

    public function onQuestionsetSaved($event)
    {
        $event = new ResourceSaved(
            new ResourceDataObject($event->questionset->id, $event->questionset->title, $event->questionset->wasRecentlyCreated === true ? ResourceSaved::CREATE : ResourceSaved::UPDATE, ResourceDataObject::QUESTIONSET),
            $event->questionset->getEdlibDataObject()
        );
        return $this->onResourceSaved($event);
    }

    public function onGameSaved($event)
    {
        $event = new ResourceSaved(
            new ResourceDataObject($event->game->id, $event->game->title, $event->game->wasRecentlyCreated === true ? ResourceSaved::CREATE : ResourceSaved::UPDATE, ResourceDataObject::GAME),
            $event->game->getEdlibDataObject()
        );
        return $this->onResourceSaved($event);
    }

    public function onResourceSaved(ResourceSaved $event)
    {
        $connection = new Vinelab\Bowler\Connection();
        $bowlerProducer = new Vinelab\Bowler\Producer($connection);
        $bowlerProducer->setup('edlibResourceUpdate', 'fanout');
        $bowlerProducer->send(json_encode($event->edlibResourceDataObject), '');

        return true;
    }
}
