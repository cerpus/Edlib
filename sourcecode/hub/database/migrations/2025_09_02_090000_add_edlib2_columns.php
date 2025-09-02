<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('contents', function (Blueprint $table) {
            $table->uuid('edlib2_id')->nullable();
            $table->index(['edlib2_id']);
            $table->unique(['edlib2_id']);

            $table->uuid('edlib2_usage_id')->nullable();
            $table->index(['edlib2_usage_id']);
            $table->unique(['edlib2_usage_id']);
        });

        DB::update(<<<SQL
        UPDATE contents SET
            edlib2_id = (
                SELECT CAST(tags.name AS UUID)
                FROM content_tag
                JOIN tags ON content_tag.tag_id = tags.id
                WHERE tags.prefix = 'edlib2_id' AND content_tag.content_id = contents.id
                LIMIT 1
            ),
            edlib2_usage_id = (
                SELECT CAST(tags.name AS UUID)
                FROM content_tag
                JOIN tags ON content_tag.tag_id = tags.id
                WHERE tags.prefix = 'edlib2_usage_id' AND content_tag.content_id = contents.id
                LIMIT 1
            )
        SQL);
    }

    public function down(): void
    {
        Schema::table('contents', function (Blueprint $table) {
            $table->dropIndex(['edlib2_id']);
            $table->dropColumn('edlib2_id');

            $table->dropIndex(['edlib2_usage_id']);
            $table->dropColumn('edlib2_usage_id');
        });
    }
};
