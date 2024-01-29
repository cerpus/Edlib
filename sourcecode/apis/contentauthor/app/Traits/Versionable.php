<?php

namespace App\Traits;

use App\ContentVersion;

trait Versionable
{
    public function fetchVersionData(): ?ContentVersion
    {
        return $this->getVersion();
    }

    public function getParent(): ?ContentVersion
    {
        return $this->getVersion()?->previousVersion;
    }

    public function getParentIds(): array
    {
        $parent = $this->getParent();
        $parentIds = [];
        if (is_object($parent)) {
            while ($parent) {
                $parentIds[] = $parent->content_id;
                $parent = $parent->previousVersion;
            }
        }

        return $parentIds;
    }

    public function getChildren(): array
    {
        return $this->getVersion()?->nextVersions->toArray();
    }

    public function getVersion(): ContentVersion|null
    {
        if ($this->{$this->getVersionColumn()}) {
            return ContentVersion::find($this->{$this->getVersionColumn()});
        }

        return null;
    }
}
