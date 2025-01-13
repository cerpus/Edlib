<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('tags', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('prefix');
            $table->string('name');
            $table->unique(['prefix', 'name']);
            $table->index('prefix');
        });

        Schema::create('content_version_tag', function (Blueprint $table) {
            $table->ulid('content_version_id');
            $table->uuid('tag_id');
            $table->text('verbatim_name')->nullable();

            $table->foreign('tag_id')->references('id')->on('tags');
            $table->foreign('content_version_id')->references('id')->on('content_versions');
        });
    }

    public function down(): void
    {
        Schema::drop('content_version_tag');
        Schema::drop('tags');
    }
};
