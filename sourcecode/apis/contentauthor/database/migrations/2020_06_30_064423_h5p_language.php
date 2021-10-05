<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class H5pLanguage extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('h5p_contents', function (Blueprint $table) {
            $table->string('language_iso_639_3', 3)->nullable()->default(null);
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
            $table->dropColumn(['language_iso_639_3']);
        });
    }
}
