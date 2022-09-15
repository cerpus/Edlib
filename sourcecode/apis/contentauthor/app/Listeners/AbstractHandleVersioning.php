<?php

namespace App\Listeners;

use App\Libraries\Versioning\VersionableObject;
use Cerpus\VersionClient\exception\LinearVersioningException;
use Cerpus\VersionClient\VersionClient;
use Cerpus\VersionClient\VersionData;
use Illuminate\Support\Facades\Log;

abstract class AbstractHandleVersioning
{
    protected $versionClient;

    public function __construct(VersionClient $versionClient)
    {
        $this->versionClient = $versionClient;
    }

    abstract protected function getParentVersionId();
    abstract protected function getExternalUrl(VersionableObject $object);

    protected function handleSave(VersionableObject $object, $reason)
    {
        if (!empty(config('feature.versioning'))) {
            $linearVersioning = config('feature.linear-versioning') ? true : false;
            $parentVersionId = $this->getParentVersionId();
            if ($parentVersionId !== null) {
                if ($object->setParentVersionId($parentVersionId)) {
                    $object->save();
                }
            }

            $versionData = $this->createVersion(
                $object,
                $parentVersionId,
                $reason,
                $linearVersioning
            );

            if ($versionData && $versionData->getParent()) {
                $parent = $versionData->getParent();
                if ($parent instanceof VersionData) {
                    $parent = $parent->getId();
                }
                if ($object->setParentVersionId($parent)) {
                    $object->save();
                }
            }

            if (!$versionData) {
                Log::error('Versioning failed: ' . $this->versionClient->getErrorCode() . ': ' . $this->versionClient->getMessage());
            //Maybe do something more constructive...add to queue to try again?
            } else {
                $object->setVersionId($versionData->getId());
                $object->save();
            }
        } else {
            Log::debug(__METHOD__ . ' Versioning not enabled. Set "FEATURE_VERSIONING=true" in .env to enable');
        }
    }

    protected function createVersion(VersionableObject $object, $parentVersionId, $reason, $linearVersioning)
    {
        $versionData = new VersionData();
        $versionData->setUserId($object->getOwnerId())
            ->setExternalReference($object->getId())
            ->setExternalSystem(config('app.site-name'))
            ->setExternalUrl($this->getExternalUrl($object))
            ->setParentId($parentVersionId)
            ->setVersionPurpose($reason)
            ->setLinearVersioning($linearVersioning);

        try {
            return $this->versionClient->createVersion($versionData);
        } catch (LinearVersioningException $e) {
            /** @var $leafs VersionData[] */
            $leafs = $e->getLeafNodes();
            /** @var $mostRecentLeaf VersionData */
            $mostRecentLeaf = null;
            foreach ($leafs as $leaf) {
                if ($mostRecentLeaf === null || $mostRecentLeaf->getCreatedAt() < $leaf->getCreatedAt()) {
                    $mostRecentLeaf = $leaf;
                }
            }

            if ($mostRecentLeaf === null) {
                throw new \Exception("No leaf nodes received for conflict resolution");
            }

            $leafId = $mostRecentLeaf->getId();

            if (!$linearVersioning) {
                Log::warning('Resource '.$object->getId().': Linear versioning exception with linear versioning disabled. This probably means that the parent '.$parentVersionId.' has linear restricted childs');
            }

            Log::warning('Resource '.$object->getId().': Linear versioning restrictions caused requested parent '.$parentVersionId.' to be replaced with leaf node '.$leafId);
            return $this->createVersion($object, $leafId, $reason, $linearVersioning);
        }
    }
}
