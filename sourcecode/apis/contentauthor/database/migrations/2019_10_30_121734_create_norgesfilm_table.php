<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateNorgesfilmTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('norgesfilms', function (Blueprint $table) {
            $table->increments('id');
            $table->string('article_title')->index();
            $table->uuid('article_id')->index();
            $table->string('article_url');
            $table->string('ndla_id');
            $table->string('ndla_url')->nullable()->default(null);
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
        Schema::dropIfExists('norgesfilms');
    }
}
