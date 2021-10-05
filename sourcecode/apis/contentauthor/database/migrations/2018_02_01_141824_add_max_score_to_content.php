<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddMaxScoreToContent extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('h5p_contents', function (Blueprint $table) {
            $table->smallInteger('max_score', false, true)->nullable()->default(0);
        });
        DB::update("UPDATE h5p_contents SET max_score = NULL");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('h5p_contents', function (Blueprint $table) {
            $table->dropColumn('max_score');
        });
    }
}
