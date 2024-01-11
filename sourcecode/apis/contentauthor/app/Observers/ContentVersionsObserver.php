<?php

namespace App\Observers;

use App\ContentVersion;
use Illuminate\Support\Facades\Log;

class ContentVersionsObserver
{
    public function saving(ContentVersion $contentVersion): bool
    {
        $parent = $contentVersion->getPreviousVersion();
        if ($parent && ($parent->linear_versioning || $contentVersion->linear_versioning)) {
            // Verify that we are continuing from the latest version
            $latest = $parent->latestVersion();
            if ($latest && $latest->id !== $parent->id) {
                $contentVersion->parent_id = $latest->id;
                Log::warning(sprintf(
                    'Version for "%s" resource "%s": Linear versioning restrictions caused requested parent "%s" to be replaced with leaf node "%s"',
                    $contentVersion->content_type,
                    $contentVersion->content_id,
                    $parent->id,
                    $latest->id
                ));
            }
        }

        return true;
    }
}
