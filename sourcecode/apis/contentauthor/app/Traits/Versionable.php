<?php

namespace App\Traits;

use Cerpus\VersionClient\VersionClient;
use Cerpus\VersionClient\VersionData;
use Illuminate\Support\Facades\Cache;

trait Versionable
{
    /** @var VersionData|null */
    private $versionData = null;

    public function fetchVersionData(): ?VersionData
    {
        $cacheKey = "versionable-data|".$this->id;
        $cacheTime = 5;

        if (!$this->versionData) {
            if (!$versionData = Cache::get($cacheKey)) {
                if ($this->version_id) {
                    $vc = app(VersionClient::class);
                    $versionData = $vc->getVersion($this->version_id);
                    if ($versionData instanceof VersionData) {
                        Cache::put($cacheKey, $versionData, now()->addSeconds($cacheTime));
                    }
                }
            }

            $this->versionData = $versionData;
        }

        if (!$this->versionData) {
            $this->versionData = new VersionData();
        }

        return $this->versionData;
    }

    public function getParent(): ?VersionData
    {
        $cacheName = "versionable-data-parent|".$this->id;
        // Parents will never change. Cache for a long time.
        $cacheTime = 3600;

        if (!$parent = Cache::get($cacheName)) {
            $parent = $this->fetchVersionData()->getParent();
            Cache::put($cacheName, $parent, now()->addSeconds($cacheTime));
        }

        return $parent;
    }

    public function getParentIds()
    {
        $parent = $this->getParent();
        $parentIds = [];
        if (is_object($parent)) {
            while ($parent) {
                $parentIds[] = $parent->getExternalReference();
                $parent = $parent->getParent();
            }
        }

        return $parentIds;
    }

    public function getChildren(): array
    {
        return $this->fetchVersionData()->getChildren();
    }
}
