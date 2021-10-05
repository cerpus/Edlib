<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddLanguageCodeToNdlaIdMappers extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('ndla_id_mappers', function (Blueprint $table) {
            $table->string('language_code', 2)->nullable()->default(null);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('ndla_id_mappers', function (Blueprint $table) {
            $table->dropColumn('language_code');
        });
    }
}
