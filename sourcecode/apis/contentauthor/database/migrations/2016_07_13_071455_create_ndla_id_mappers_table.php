<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateNdlaIdMappersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ndla_id_mappers', function (Blueprint $table) {
            $table->increments('id');
            $table->string('ndla_id', 40)->index();
            $table->string('ca_id', 40)->index();
            $table->string('type', 10)->index();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('ndla_id_mappers');
    }
}
