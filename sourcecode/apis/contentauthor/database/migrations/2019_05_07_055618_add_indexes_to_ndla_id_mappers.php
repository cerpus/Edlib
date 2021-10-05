<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddIndexesToNdlaIdMappers extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('ndla_id_mappers', function (Blueprint $table) {
            $table->index('launch_url');
            $table->index(['ndla_id', 'language_code', 'type']);
            $table->index('ndla_checksum');
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
            $table->dropIndex(['launch_url']);
            $table->dropIndex(['ndla_id', 'language_code', 'type']);
            $table->dropIndex(['ndla_checksum']);
        });
    }
}
