<?php

namespace App\Traits;

use App\ContentVersions;

trait Versionable
{
    public function fetchVersionData(): ?ContentVersions
    {
        return $this->getVersion();
    }

    public function getParent(): ?ContentVersions
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

    public function getVersion(): ContentVersions|null
    {
        if ($this[$this->getVersionColumn()]) {
            return ContentVersions::find($this[$this->getVersionColumn()]);
        }

        return null;
    }
}
