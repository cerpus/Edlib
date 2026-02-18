<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('content_exclusions', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->ulid('content_id');
            $table->string('exclude_from');
            $table->string('user_id')->nullable();
            $table->timestampTz('created_at')->nullable();

            $table->foreign('content_id')->references('id')->on('contents')->cascadeOnDelete();
            $table->unique(['content_id', 'exclude_from']);

            $table->index('content_id');
        });
    }

    public function down(): void
    {
        Schema::drop('content_exclusions');
    }
};
