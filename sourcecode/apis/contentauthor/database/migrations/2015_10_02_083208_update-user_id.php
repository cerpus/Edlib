<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateUserId extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('h5p_contents', function ($table) {
            $table->string('user_id',36)->change();
        });
        Schema::table('h5p_contents_user_data', function ($table) {
            $table->string('user_id',36)->change();
        });
        Schema::table('h5p_results', function ($table) {
            $table->string('user_id',36)->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('h5p_contents', function ($table) {
            $table->integer('user_id')->unsigned()->change();
        });
        Schema::table('h5p_contents_user_data', function ($table) {
            $table->integer('user_id')->unsigned()->change();
        });
        Schema::table('h5p_results', function ($table) {
            $table->integer('user_id')->unsigned()->change();
        });

    }
}
