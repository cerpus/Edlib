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
            $table->text('lti_launch_url')->nullable();
            $table->ulid('lti_tool_id')->nullable();
            $table->text('title')->nullable();
            $table->text('title_html')->nullable();
            $table->string('license')->nullable();
            $table->string('language_iso_639_3')->default('und');

            $table->foreign('lti_tool_id')->references('id')->on('lti_tools');
        });

        if (DB::table('content_versions')->count()) {
            DB::update(<<<EOSQL
            UPDATE content_versions cv
            SET
                lti_launch_url = r.view_launch_url,
                lti_tool_id = r.lti_tool_id,
                title = r.title,
                title_html = r.title_html,
                license = r.license,
                language_iso_639_3 = r.language_iso_639_3
            FROM lti_resources r
            WHERE cv.lti_resource_id = r.id
            EOSQL);
        }

        Schema::table('content_versions', function (Blueprint $table) {
            $table->text('lti_launch_url')->nullable(false)->change();
            $table->text('lti_tool_id')->nullable(false)->change();
            $table->text('title')->nullable(false)->change();
            $table->dropColumn('lti_resource_id');
        });

        Schema::drop('lti_resources');
    }
};
