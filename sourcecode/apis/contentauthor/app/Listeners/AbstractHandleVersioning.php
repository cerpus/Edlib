<?php

namespace App\Listeners;

use App\ContentVersion;
use App\Libraries\Versioning\VersionableObject;
use Illuminate\Support\Facades\Log;

abstract class AbstractHandleVersioning
{
    abstract protected function getParentVersionId();

    protected function handleSave(VersionableObject $object, $reason)
    {
        $linearVersioning = (bool) config('feature.linear-versioning');
        $parentVersionId = $this->getParentVersionId();
        if ($parentVersionId !== null) {
            if ($object->setParentVersionId($parentVersionId)) {
                $object->save();
            }
        }

        /** @var ?ContentVersion $versionData */
        $versionData = ContentVersion::create([
            'user_id' => $object->getOwnerId(),
            'content_id' => $object->getId(),
            'content_type' => $object->getContentType(),
            'parent_id' => $parentVersionId,
            'version_purpose' => $reason,
            'linear_versioning' => $linearVersioning,
        ]);

        if ($versionData) {
            $object->setVersionId($versionData->id);
            $parent = $versionData->previousVersion;
            if ($parent) {
                $object->setParentVersionId($parent->id);
            }
            $object->save();
        } else {
            Log::error('Versioning failed', [$object, $reason]);
        }
    }
}
