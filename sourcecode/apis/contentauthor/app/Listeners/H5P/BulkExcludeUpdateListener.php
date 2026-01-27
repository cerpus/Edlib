<?php

declare(strict_types=1);

namespace App\Listeners\H5P;

use App\ContentVersion;
use App\Events\H5PWasSaved;
use Illuminate\Support\Facades\DB;

/**
 * Update exclude content id if content was saved with new id and version purpose is
 * ContentVersion::PURPOSE_UPDATE or ContentVersion::PURPOSE_UPGRADE
 */
final readonly class BulkExcludeUpdateListener {
    public function handle(H5PWasSaved $event): void
    {
        if (
            $event->oldH5pContent !== null &&
            $event->oldH5pContent->id !== $event->h5p->id &&
            in_array($event->versionPurpose, [ContentVersion::PURPOSE_UPDATE, ContentVersion::PURPOSE_UPGRADE]) &&
            $event->oldH5pContent->exclutions()->count() > 0
        ) {
            DB::table('content_bulk_excludes')
                ->where('content_id', $event->oldH5pContent->id)
                ->update(['content_id' => $event->h5p->id]);
        }
    }
}
