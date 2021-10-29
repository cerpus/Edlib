<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateContentTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('content', function (Blueprint $table) {
            $table->increments('id');
            $table->timestamps();
            $table->string('site', 256);
            $table->string('content_id', 1024);
            $table->string('content_id_hash', 128);
            $table->text('name');
            $table->index('content_id', 'ind_content_id');
            $table->index(['site', 'content_id_hash'], 'ind_site_content_id_hash');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('content');
    }
}
