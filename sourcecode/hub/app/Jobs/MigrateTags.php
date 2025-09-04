<?php

declare(strict_types=1);

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\DB;

/**
 * One-time job to migrate tags over to discrete columns.
 */
class MigrateTags implements ShouldQueue, ShouldBeUnique
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;

    public function handle(): void
    {
        $this->updateDisplayedContentType();

        $this->updateEdlib2Columns();
    }

    private function updateDisplayedContentType(): void
    {
        DB::update(<<<SQL
        UPDATE content_versions
        SET displayed_content_type = (
                SELECT COALESCE(content_version_tag.verbatim_name, tags.name)
                FROM content_version_tag
                JOIN tags ON content_version_tag.tag_id = tags.id
                WHERE tags.prefix = 'h5p' AND content_version_tag.content_version_id = content_versions.id
                LIMIT 1
            ),
            displayed_content_type_normalized = (
                SELECT tags.name
                FROM content_version_tag
                JOIN tags ON content_version_tag.tag_id = tags.id
                WHERE tags.prefix = 'h5p' AND content_version_tag.content_version_id = content_versions.id
                LIMIT 1
            )
        WHERE displayed_content_type IS NULL
        SQL);
    }

    private function updateEdlib2Columns(): void
    {
        DB::update(<<<SQL
        UPDATE contents SET edlib2_id = (
            SELECT CAST(tags.name AS UUID)
            FROM content_tag
            JOIN tags ON content_tag.tag_id = tags.id
            WHERE tags.prefix = 'edlib2_id' AND content_tag.content_id = contents.id
            LIMIT 1
        ) WHERE edlib2_id IS NULL
        SQL);

        DB::update(<<<SQL
        INSERT INTO content_edlib2_usages (content_id, edlib2_usage_id)
        SELECT content_tag.content_id, CAST(tags.name AS UUID)
        FROM content_tag
        JOIN tags ON content_tag.tag_id = tags.id
        WHERE tags.prefix = 'edlib2_usage_id';
        SQL);
    }
}
