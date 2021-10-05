<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddFetchFlagForMetadataFromDrupal extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('ndla_id_mappers', function (Blueprint $table){
            $table->smallInteger('metadata_fetch')->nullable()->default(null);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('ndla_id_mappers', function (Blueprint $table){
            $table->dropColumn('metadata_fetch');
        });
    }
}
