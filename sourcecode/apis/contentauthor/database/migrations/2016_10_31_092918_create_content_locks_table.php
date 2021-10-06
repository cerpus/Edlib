<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateContentLocksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('content_locks', function (Blueprint $table) {
            $table->uuid('content_id')->index();
            $table->uuid('auth_id');
            $table->string('email')->nullable();
            $table->string('name')->nullable();
            $table->timestamps();

            $table->index(['content_id', 'auth_id']);
            $table->index(['content_id', 'auth_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('content_locks');
    }
}
