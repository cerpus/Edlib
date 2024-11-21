<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('contexts', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->string('name');
            $table->timestampsTz();
        });

        Schema::create('content_context', function (Blueprint $table) {
            $table->ulid('content_id');
            $table->ulid('context_id');
            $table->string('role');
            $table->timestampsTz();

            $table->foreign('content_id')->references('id')->on('contents');
            $table->foreign('context_id')->references('id')->on('contexts');
        });
    }

    public function down(): void
    {
        Schema::drop('content_context');
        Schema::drop('contexts');
    }
};
