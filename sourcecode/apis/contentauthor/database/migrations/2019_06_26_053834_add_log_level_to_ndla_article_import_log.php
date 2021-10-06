<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddLogLevelToNdlaArticleImportLog extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('ndla_article_import_statuses', function (Blueprint $table) {
            $table->unsignedSmallInteger('log_level')->default(100); // Defaults to debug
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('ndla_article_import_statuses', function (Blueprint $table) {
            $table->dropColumn('log_level');
        });
    }
}
