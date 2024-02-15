<?php

namespace App\Listeners\Link;

use App\Events\LinkWasSaved;
use App\Listeners\AbstractHandleVersioning;

class HandleVersioning extends AbstractHandleVersioning
{
    protected $link;

    public function handle(LinkWasSaved $event)
    {
        $this->link = $event->link->fresh();

        $this->handleSave($this->link, $event->reason);
    }

    public function getParentVersionId()
    {
        return $this->link->version_id;
    }
}
