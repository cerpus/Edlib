<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('content_versions', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->ulid('content_id');
            $table->ulid('lti_resource_id');
            $table->ulid('parent_version_id')->nullable();
            $table->boolean('published');
            $table->timestampTz('created_at');

            $table->foreign('content_id')->references('id')->on('contents');
            $table->foreign('lti_resource_id')->references('id')->on('lti_resources');
        });

        Schema::table('content_versions', function (Blueprint $table) {
            $table->foreign('parent_version_id')->references('id')->on('content_versions');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('content_versions');
    }
};
