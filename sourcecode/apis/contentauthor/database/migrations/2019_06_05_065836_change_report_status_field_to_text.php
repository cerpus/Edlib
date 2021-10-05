<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangeReportStatusFieldToText extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('ndla_article_import_statuses', function (Blueprint $table) {
            $table->text('message')->change();
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
            $table->string('message')->change();
        });
    }
}
