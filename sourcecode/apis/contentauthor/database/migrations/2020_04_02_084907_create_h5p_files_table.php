<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateH5pFilesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('h5p_files', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->timestamps();
            $table->string('filename');
            $table->string('user_id');
            $table->string('state')->default(\App\Libraries\H5P\EditorStorage::FILE_TEMPORARY);
            $table->string('file_hash')->nullable()->default(null);
            $table->string('external_reference')->nullable()->default(null);
            $table->unsignedInteger('content_id')->nullable()->default(null);
            $table->foreign(['content_id'])->references('id')->on('h5p_contents');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('h5p_files');
    }
}
