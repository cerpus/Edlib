<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateContentLicenseTable extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('content_license', function (Blueprint $table) {
            $table->increments('id');
            $table->timestamps();
            $table->integer('content_id')->unsigned();
            $table->string('license_id', 64);
            $table->foreign('content_id', 'fk_content_license_content')->references('id')->on('content');
            $table->unique(['content_id', 'license_id'], 'uniq_content_license_content_license');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('content_license');
    }
}