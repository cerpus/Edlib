<?php

namespace App\Observers;

use App\ContentVersion;
use Illuminate\Support\Facades\Log;

class ContentVersionsObserver
{
    public function saving(ContentVersion $contentVersion): bool
    {
        $parent = $contentVersion->previousVersion;
        if ($parent && ($parent->linear_versioning || $contentVersion->linear_versioning) && !$parent->isLeaf()) {
            // Parent is not a leaf node, so find latest leaf to use as parent
            $latest = $parent->latestLeafVersion();
            if ($latest !== null && $latest->id !== $parent->id) {
                $contentVersion->parent_id = $latest->id;
                Log::warning(sprintf(
                    'Version for "%s" resource "%s": Linear versioning restrictions caused requested parent "%s" to be replaced with leaf node "%s"',
                    $contentVersion->content_type,
                    $contentVersion->content_id,
                    $parent->id,
                    $latest->id,
                ));
            }
        }

        return true;
    }
}
