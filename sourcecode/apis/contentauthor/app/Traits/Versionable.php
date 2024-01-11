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
        return $this->getVersion()?->getPreviousVersion();
    }

    public function getParentIds(): array
    {
        $parent = $this->getParent();
        $parentIds = [];
        if (is_object($parent)) {
            while ($parent) {
                $parentIds[] = $parent->content_id;
                $parent = $parent->getPreviousVersion();
            }
        }

        return $parentIds;
    }

    public function getChildren(): array
    {
        return $this->getVersion()?->getNextVersions()->toArray();
    }

    public function getVersion(): ContentVersion|null
    {
        if ($this->{$this->getVersionColumn()}) {
            return ContentVersion::find($this->{$this->getVersionColumn()});
        }

        return null;
    }
}
