<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

/**
 * Job to switch between displaying H5P content type machine name or H5P content type title
 */
class SwapH5PTypeDisplayName implements ShouldQueue, ShouldBeUnique
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;

    public function handle(): void
    {
        $displayType = Config::get('features.ca-content-type-display', '');
        if (!in_array($displayType, ['h5p', 'h5p_title'])) {
            $this->fail('SwapH5PTypeDisplayName job failed, invalid config value: ' . $displayType);
        } else {
            $this->updateDisplayedContentType($displayType);
        }
    }

    private function updateDisplayedContentType(string $prefix): void
    {
        DB::update(<<<SQL
        UPDATE content_versions
        SET displayed_content_type = (
                SELECT COALESCE(content_version_tag.verbatim_name, tags.name)
                FROM content_version_tag
                JOIN tags ON content_version_tag.tag_id = tags.id
                WHERE tags.prefix = '{$prefix}'
                AND content_version_tag.content_version_id = content_versions.id
                LIMIT 1
            ),
            displayed_content_type_normalized = (
                SELECT LOWER(tags.name)
                FROM content_version_tag
                JOIN tags ON content_version_tag.tag_id = tags.id
                WHERE tags.prefix = '{$prefix}'
                AND content_version_tag.content_version_id = content_versions.id
                LIMIT 1
            )
        SQL);
    }
}
