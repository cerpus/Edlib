<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddParentIdAndParentVersionIdFieldsToArticle extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('articles', function (Blueprint $table) {
            $table->uuid('parent_id')->index()->nullable()->after('id');
        });

        Schema::table('articles', function (Blueprint $table) {
            $table->uuid('parent_version_id')->nullable()->after('parent_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('articles', function (Blueprint $table) {
            $table->dropColumn('parent_id');
        });

        Schema::table('articles', function (Blueprint $table) {
            $table->dropColumn('parent_version_id');
        });
    }
}
