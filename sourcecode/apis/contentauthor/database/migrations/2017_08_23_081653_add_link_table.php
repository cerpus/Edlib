<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddLinkTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('links', function (Blueprint $table) {
            $table->uuid('id');
            $table->string('title')->nullable();
            $table->text("link_url");
            $table->string('link_type')->default("external_link");
            $table->string('owner_id')->nullable();
            $table->string('version_id')->nullable();

            $table->timestampsTz();
            $table->softDeletesTz();

            $table->primary("id");
            $table->index(['link_type']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('links');
    }
}
