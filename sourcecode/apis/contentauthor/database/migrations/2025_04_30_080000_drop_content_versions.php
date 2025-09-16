<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\SQLiteConnection;

return new class extends Migration {
    public function up(): void
    {
        $this->migrateArticleVersions();
        $this->migrateGameVersions();
        $this->migrateH5pVersions();
        $this->migrateLinkVersions();

        Schema::drop('content_versions');
    }

    private function migrateArticleVersions(): void
    {
        Schema::table('articles', function (Blueprint $table) {
            // has a parent_id already
            $table->string('version_purpose')->default('Initial');
        });

        if (!DB::connection() instanceof SQLiteConnection) {
            DB::update(<<<EOSQL
            UPDATE articles
                JOIN content_versions ON content_versions.id = articles.version_id
            SET articles.version_purpose = content_versions.version_purpose;
            EOSQL);
        }

        Schema::table('articles', function (Blueprint $table) {
            $table->dropColumn('version_id');
            $table->dropColumn('parent_version_id');
        });
    }

    private function migrateGameVersions(): void
    {
        Schema::table('games', function (Blueprint $table) {
            $table->uuid('parent_id')->nullable();
            $table->string('version_purpose')->default('Initial');

            $table->foreign('parent_id')->references('id')->on('games');
        });

        if (!DB::connection() instanceof SQLiteConnection) {
            DB::update(<<<EOSQL
            UPDATE games
                JOIN content_versions self ON self.id = games.version_id
                LEFT JOIN content_versions parent ON parent.id = self.parent_id
                LEFT JOIN games parent_content ON parent_content.version_id = parent.id
            SET games.parent_id = parent_content.id,
                games.version_purpose = self.version_purpose;
            EOSQL);
        }

        Schema::table('games', function (Blueprint $table) {
            $table->dropIndex('games_version_id_index');
            $table->dropColumn('version_id');
        });
    }

    private function migrateH5pVersions(): void
    {
        Schema::table('h5p_contents', function (Blueprint $table) {
            $table->unsignedInteger('parent_id')->nullable();
            $table->string('version_purpose')->default('Initial');

            $table->foreign('parent_id')->references('id')->on('h5p_contents');
        });

        if (!DB::connection() instanceof SQLiteConnection) {
            DB::update(<<<EOSQL
            UPDATE h5p_contents
                JOIN content_versions self ON self.id = h5p_contents.version_id
                LEFT JOIN content_versions parent ON parent.id = self.parent_id
                LEFT JOIN h5p_contents parent_content ON parent_content.version_id = parent.id
            SET h5p_contents.parent_id = parent_content.id,
                h5p_contents.version_purpose = self.version_purpose;
            EOSQL);
        }

        Schema::table('h5p_contents', function (Blueprint $table) {
            $table->dropIndex('h5p_contents_version_id_index');
            $table->dropColumn('version_id');
        });
    }

    private function migrateLinkVersions(): void
    {
        Schema::table('links', function (Blueprint $table) {
            $table->uuid('parent_id')->nullable();
            $table->string('version_purpose')->default('Initial');

            $table->foreign('parent_id')->references('id')->on('links');
        });

        if (!DB::connection() instanceof SQLiteConnection) {
            DB::update(<<<EOSQL
            UPDATE links
                JOIN content_versions self ON self.id = links.version_id
                LEFT JOIN content_versions parent ON parent.id = self.parent_id
                LEFT JOIN links parent_content ON parent_content.version_id = parent.id
            SET links.parent_id = parent_content.id,
                links.version_purpose = self.version_purpose;
            EOSQL);
        }

        Schema::table('links', function (Blueprint $table) {
            $table->dropColumn('version_id');
        });
    }
};
