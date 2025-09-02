<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('content_versions', function (Blueprint $table) {
            $table->string('displayed_content_type')->nullable();
            $table->string('displayed_content_type_normalized')->nullable();
            $table->index(['displayed_content_type_normalized']);
        });

        DB::update(<<<SQL
        UPDATE content_versions
        SET displayed_content_type = (
                SELECT content_version_tag.verbatim_name
                FROM content_version_tag
                JOIN tags ON content_version_tag.tag_id = tags.id
                WHERE tags.prefix = 'h5p' AND content_version_tag.content_version_id = content_versions.id
                LIMIT 1
            ),
            displayed_content_type_normalized = (
                SELECT LOWER(content_version_tag.verbatim_name)
                FROM content_version_tag
                JOIN tags ON content_version_tag.tag_id = tags.id
                WHERE tags.prefix = 'h5p' AND content_version_tag.content_version_id = content_versions.id
                LIMIT 1
            )
        SQL);
    }

    public function down(): void
    {
        Schema::table('content_versions', function (Blueprint $table) {
            $table->dropIndex(['displayed_content_type_normalized']);
            $table->dropColumn('displayed_content_type');
            $table->dropColumn('displayed_content_type_normalized');
        });
    }
};
