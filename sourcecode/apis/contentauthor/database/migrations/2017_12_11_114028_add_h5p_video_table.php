<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddH5pVideoTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('h5p_contents_video', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('h5p_content_id');
            $table->string('video_id');
            $table->string('source_file');

            $table->timestamps();
            $table->index(['video_id']);

            $table->foreign(['h5p_content_id'])->references('id')->on('h5p_contents');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('h5p_contents_video');
    }
}
