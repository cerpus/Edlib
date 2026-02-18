<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('content_locks', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->ulid('content_id');
            $table->ulid('user_id');
            $table->timestampsTz();

            $table->foreign('content_id')->references('id')->on('contents');
            $table->foreign('user_id')->references('id')->on('users');
            $table->unique('content_id');

            $table->index('content_id');
            $table->index('user_id');
        });
    }

    public function down(): void
    {
        Schema::drop('content_locks');
    }
};
