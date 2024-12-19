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
            $table->string('name')->unique();
            $table->timestampTz('created_at');
        });

        Schema::create('content_context', function (Blueprint $table) {
            $table->ulid('content_id');
            $table->ulid('context_id');
            $table->string('role');

            $table->foreign('content_id')->references('id')->on('contents');
            $table->foreign('context_id')->references('id')->on('contexts');
            $table->unique(['content_id', 'context_id']);
        });

        Schema::create('lti_platform_context', function (Blueprint $table) {
            $table->ulid('lti_platform_id');
            $table->ulid('context_id');
            $table->string('role');

            $table->foreign('lti_platform_id')->references('id')->on('lti_platforms');
            $table->foreign('context_id')->references('id')->on('contexts');
            $table->unique(['lti_platform_id', 'context_id']);
        });
    }

    public function down(): void
    {
        Schema::drop('content_context');
        Schema::drop('lti_platform_context');
        Schema::drop('contexts');
    }
};
