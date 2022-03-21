<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIsDraft extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('h5p_contents', function (Blueprint $table) {
            $table->boolean('is_draft')->default(0);
        });

        Schema::table('articles', function (Blueprint $table) {
            $table->boolean('is_draft')->default(0);
        });

        Schema::table('games', function (Blueprint $table) {
            $table->boolean('is_draft')->default(0);
        });

        Schema::table('links', function (Blueprint $table) {
            $table->boolean('is_draft')->default(0);
        });

        Schema::table('question_sets', function (Blueprint $table) {
            $table->boolean('is_draft')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('h5p_contents', function (Blueprint $table) {
            $table->dropColumn(['is_draft']);
        });

        Schema::table('articles', function (Blueprint $table) {
            $table->dropColumn(['is_draft']);
        });

        Schema::table('games', function (Blueprint $table) {
            $table->dropColumn(['is_draft']);
        });

        Schema::table('links', function (Blueprint $table) {
            $table->dropColumn(['is_draft']);
        });

        Schema::table('question_sets', function (Blueprint $table) {
            $table->dropColumn(['is_draft']);
        });
    }
}
