<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ContentLanguageLinking extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('content_language_links', function (Blueprint $table) {
            $table->create();
            $table->increments("id");
            $table->string("main_content_id", 40);
            $table->string("link_content_id", 40)->nullable();
            $table->string("language_code", 10);
            $table->string("content_type", 10);

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
        Schema::table('content_language_links', function (Blueprint $table) {
            $table->dropIfExists();
        });
    }
}
