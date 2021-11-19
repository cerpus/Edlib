<?php


namespace App\Traits;


// Must be used in conjunction with the Versionable trait

use App\Article;
use App\Content;
use App\QuestionSet;
use Cerpus\REContentClient\REContent;
use Cerpus\VersionClient\VersionData;
use Illuminate\Support\Facades\Cache;

trait Recommendable
{
    public function isListedOnMarketplace()
    {
        return $this->isPublished();
    }

    public function scopeListedOnMarketplace($query)
    {
        $query->where("is_private", false);
    }

    public function isUsableByEveryone(): bool
    {
        return $this->isListedOnMarketplace() && $this->isCopyable();
    }

    public function hasPublicParents(): bool
    {
        return !empty($this->getPublicParentsIds());
    }

    // Get a list of parents that is visible for everyone
    public function getPublicParentsIds(): array
    {
        $cacheName = "recommendable-public-parents|".$this->id;
        $cacheTime = 5;

        if (empty($publicParents = Cache::get($cacheName, []))) {
            $parentIds = [];
            if ($currentParent = $this->getParent()) {
                while ($currentParent) {
                    $parentIds[] = $currentParent->getExternalReference();
                    $currentParent = $currentParent->getParent();
                }
            }

            $publicParents = [];

            foreach ($parentIds as $parentId) {
                if ($content = Content::findContentById($parentId)) {
                    if ($content->isUsableByEveryone()) {
                        if ($p = Content::findContentById($parentId)) {
                            $publicParents[] = $p->getPublicId();
                        }
                        unset($p);
                    }
                    unset($content);
                }
            }

            Cache::put($cacheName, $publicParents, now()->addSeconds($cacheTime));
        }

        return $publicParents;
    }

    public function getPublicChildrenIds(): array
    {
        $children = $this->getChildren();
        if (empty($children)) {
            return [];
        }

        // 1. get all ids of children
        $childrenIds = $this->getExternalReferences($children);

        // 2. Determine if some of the children are already public
        $publicChildren = [];
        foreach ($childrenIds as $childId) {
            if ($content = Content::findContentById($childId)) {
                if ($content->isUsableByEveryone()) {
                    $publicChildren[] = $childId;
                }
                unset($content);
            }
        }

        return $publicChildren;
    }

    public function getExternalReferences(array $children): array
    {
        if (empty($children)) {
            return [];
        }

        $childIds = [];

        foreach ($children as $child) {
            if ($content = Content::findContentById($child->getExternalReference())) {
                $childIds[] = $content->getPublicId();
            }
            if (!empty($child->getChildren())) {
                $childIds[] = $this->getExternalReferences($child->getChildren());
            }
        }

        return array_unique(array_flatten($childIds));
    }

    public function hasPublicChildren(): bool
    {
        return !empty($this->getPublicChildrenIds());
    }

    // This method is used to retrieve the ID used to identify content.
    // I suspect this may change
    public function getPublicId()
    {
        return $this->id;
    }

    /**
     * Determine which action should be performed in the Recommendation Engine Content Index
     *
     * @return string The action that should be performed
     */
    public function determineREAction(): string
    {
        // Exclude Questionsets from recommendation engine?
        $excludedContentTypes = [QuestionSet::class];
        if (in_array(get_class($this), $excludedContentTypes)) {
            return self::RE_ACTION_NOOP;
        }

        $hasPublicChildren = $this->hasPublicChildren();
        $isUsableByEveryone = $this->isUsableByEveryone();

        if (!$hasPublicChildren
            && $isUsableByEveryone) {
            return self::RE_ACTION_UPDATE_OR_CREATE;
        }

        if ($hasPublicChildren
            || !$isUsableByEveryone) {
            return self::RE_ACTION_REMOVE;
        }

        return self::RE_ACTION_NOOP;
    }

    public function toREContent(): REContent
    {
        $reContent = app(REContent::class);

        $reContent->setId($this->getPublicId());
        $reContent->setTitle($this->title);
        $reContent->setType($this->getContentType(true));
        $reContent->setLicense($this->getContentLicense());
        $reContent->setLastUpdatedAt($this->updated_at);

        if (!empty($parentIds = $this->getParentIds())) {
            $prevPublicId = Content::findContentById($parentIds[0])->getPublicId();
            $reContent->setPreviousVersion($prevPublicId);
        }

        if ($this instanceof Article) {
            $reContent->setContent($this->content);
        }

        return $reContent;
    }
}
