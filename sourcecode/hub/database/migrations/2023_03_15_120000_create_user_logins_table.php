<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('user_logins', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->ulid('user_id');
            $table->string('email');
            $table->string('password');
            $table->timestampsTz();

            $table->foreign('user_id')->references('id')->on('users');
            $table->unique('email');
            $table->unique('user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_logins');
    }
};
