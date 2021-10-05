<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateLibraryDescriptionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('library_descriptions', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('library_id')->unsigned();
            $table->string('locale')->default('en-gb')->index();
            $table->string('title');
            $table->string('description');
            $table->timestamps();

            $table->index(['library_id', 'locale']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('library_descriptions');
    }
}
