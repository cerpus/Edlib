<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateH5PLibraryCapabilitiesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('h5p_library_capabilities', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('library_id')->unsigned();
            $table->string('name');
            $table->tinyInteger('score')->default(0);
            $table->tinyInteger('enabled')->default(1);
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
        Schema::drop('h5p_library_capabilities');
    }
}
