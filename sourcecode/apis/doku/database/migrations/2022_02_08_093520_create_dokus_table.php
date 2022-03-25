<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDokusTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('dokus', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->text('title');
            $table->uuid('creator_id');
            $table->json('data');
            $table->boolean('draft')->default(true);
            $table->boolean('public')->default(false);
            $table->timestamps();
            // FIXME: should be non-nullable, but mariadb hates this.
            $table->timestamp('edit_allowed_until')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('dokus');
    }
}
