<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTableRefidSupport extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('h5p_refid_support', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->timestamps();
            $table->integer('content_id');
            $table->string('title');
            $table->boolean('processed')->default(0);
            $table->boolean('istarget')->default(0);
            $table->unique(['content_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('h5p_refid_support');
    }
}
