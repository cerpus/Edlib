<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('content_versions', function (Blueprint $table) {
            $table->ulid('edited_by')->nullable();
            $table->foreign('edited_by')->references('id')->on('users');
        });
    }

    public function down(): void
    {
        Schema::table('content_versions', function (Blueprint $table) {
            $table->dropColumn('edited_by');
        });
    }
};
