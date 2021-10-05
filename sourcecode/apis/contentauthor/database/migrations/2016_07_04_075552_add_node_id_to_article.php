<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddNodeIdToArticle extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('articles', function (Blueprint $table) {
            $table->string('node_id')->nullable()->default(null);
        });

        Schema::table('articles', function (Blueprint $table) {
            $table->string('ndla_url')->nullable()->default(null);
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
            $table->dropColumn('node_id');
        });

        Schema::table('articles', function (Blueprint $table) {
            $table->dropColumn('ndla_url');
        });

    }
}
