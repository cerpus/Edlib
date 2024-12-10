<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddH5pHubCache extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('h5p_libraries_hub_cache', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name', 127);
            $table->unsignedTinyInteger('major_version');
            $table->unsignedTinyInteger('minor_version');
            $table->unsignedTinyInteger('patch_version');
            $table->unsignedTinyInteger('h5p_major_version');
            $table->unsignedTinyInteger('h5p_minor_version');
            $table->string('title');
            $table->text('summary');
            $table->text('description');
            $table->string('icon', 511);
            $table->unsignedTinyInteger('is_recommended');
            $table->unsignedSmallInteger('popularity');
            $table->text('screenshots');
            $table->text('license');
            $table->string('example', 511);
            $table->string('tutorial', 511);
            $table->text('keywords');
            $table->text('categories');
            $table->string('owner');

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
        Schema::dropIfExists('h5p_libraries_hub_cache');
    }
}
