<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class DontImportDuplicates extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('ndla_id_mappers', function (Blueprint $table) {
            $table->string('ndla_checksum')->nullable()->default(null);
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
            $table->dropColumn("ndla_checksum");
        });
    }
}
