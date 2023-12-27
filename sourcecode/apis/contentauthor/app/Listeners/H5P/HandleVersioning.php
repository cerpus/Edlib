<?php

namespace App\Listeners\H5P;

use App\Content;
use App\ContentVersions;
use App\Events\H5PWasSaved;
use App\H5PContent;
use App\Listeners\AbstractHandleVersioning;

class HandleVersioning extends AbstractHandleVersioning
{
    protected $h5p;
    protected $event;

    public function handle(H5PWasSaved $event)
    {
        $this->h5p = $event->h5p->fresh();
        $this->event = $event;

        $this->handleSave($this->h5p, $event->versionPurpose);
    }

    protected function getVersionId($id)
    {
        if (!empty($id)) {
            $h5p = H5PContent::find($id);

            if (is_object($h5p)) {
                if (empty($h5p->version_id)) {
                    $version = ContentVersions::create([
                        'user_id' => $h5p->user_id,
                        'content_id' => $h5p->id,
                        'content_type' => Content::TYPE_H5P,
                        'version_purpose' => ContentVersions::PURPOSE_CREATE,
                    ]);
                    $h5p->version_id = $version->id;
                    $h5p->save();
                }

                return $h5p->version_id;
            }
        }

        return false;
    }

    protected function getParentVersionId()
    {
        $parentVersionId = $this->event->oldH5pContent !== null ? $this->getVersionId($this->event->oldH5pContent->id) : null;
        if ($parentVersionId) {
            return $parentVersionId;
        } else {
            return null;
        }
    }
}
