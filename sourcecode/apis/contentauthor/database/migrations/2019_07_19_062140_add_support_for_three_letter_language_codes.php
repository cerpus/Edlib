<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddSupportForThreeLetterLanguageCodes extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('content_languages', function (Blueprint $table) {
            $table->string('language_code', 3)->change();
        });

        Schema::table('ndla_id_mappers', function (Blueprint $table) {
            $table->string('language_code', 3)->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('content_languages', function (Blueprint $table) {
            $table->string('language_code', 2)->change();
        });

        Schema::table('ndla_id_mappers', function (Blueprint $table) {
            $table->string('language_code', 2)->change();
        });
    }
}
