<?php


namespace App\Listeners\H5P;


use App\Events\H5PWasSaved;
use App\H5PContent;
use App\Libraries\Versioning\VersionableObject;
use App\Listeners\AbstractHandleVersioning;
use Cerpus\VersionClient\VersionClient;
use Cerpus\VersionClient\VersionData;

class HandleVersioning extends AbstractHandleVersioning
{
    protected $versionClient, $h5p, $event;

    public function __construct(VersionClient $versionClient)
    {
        $this->versionClient = $versionClient;
    }

    public function handle(H5PWasSaved $event) {
        $this->h5p = $event->h5p->fresh();
        $this->event = $event;

        $this->handleSave($this->h5p, $event->versionPurpose);
    }

    protected function getVersionId($id)
    {
        if (!empty($id)) {
            $h5p = H5PContent::find($id);
            $versionClient = app(VersionClient::class);

            if (is_object($h5p)) {
                if (!empty($h5p->version_id)) {
                    return $h5p->version_id;
                }

                // Does not have version, ergo we are just beginning versioning, create  a new version for this id
                $versionData = new VersionData();
                $versionData->setUserId($h5p->user_id)
                    ->setExternalReference($h5p->id)
                    ->setExternalSystem(config('app.site-name'))
                    ->setExternalUrl(route('h5p.show', $h5p->id))
                    ->setVersionPurpose(VersionData::CREATE);

                $version = $versionClient->createVersion($versionData);
                if (is_object($version)) {
                    $h5p->version_id = $version->getId();
                    $h5p->save();

                    return $h5p->version_id;
                }
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

    protected function getExternalUrl(VersionableObject $object)
    {
        return route('h5p.show', $object->getId());
    }
}
