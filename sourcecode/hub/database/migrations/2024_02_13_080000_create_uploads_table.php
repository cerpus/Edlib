<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('uploads', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->string('path');
            $table->string('mime_type');
            $table->char('hash_sha256', 64)->unique();
        });

        Schema::table('content_versions', function (Blueprint $table) {
            $table->ulid('icon_upload_id')->nullable();
            $table->foreign('icon_upload_id')->references('id')->on('uploads');
        });
    }

    public function down(): void
    {
        Schema::table('content_versions', function (Blueprint $table) {
            $table->dropColumn('icon_upload_id');
        });

        Schema::dropIfExists('uploads');
    }
};
