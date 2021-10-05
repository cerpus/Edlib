<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateNdlaArticleIds extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ndla_article_ids', function (Blueprint $table) {
            $table->bigInteger('id')->primary();
            $table->string('title');
            $table->string('language', 2);
            $table->string('type');
            $table->longText('json');

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
        Schema::dropIfExists('ndla_article_ids');
    }
}
