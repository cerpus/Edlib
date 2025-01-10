<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('content_views', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->ulid('content_id');
            $table->ulid('lti_platform_id')->nullable();
            $table->enum('source', ['embed', 'detail', 'lti_platform', 'standalone']);
            $table->ipAddress('ip')->nullable();
            $table->timestampTz('created_at');

            $table->foreign('content_id')->references('id')->on('contents');
            $table->foreign('lti_platform_id')->references('id')->on('lti_platforms');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('content_views');
    }
};
