<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('content_versions', function (Blueprint $table) {
            $table->string('displayed_content_type')->nullable();
            $table->string('displayed_content_type_normalized')->nullable();
            $table->index(['displayed_content_type_normalized']);
        });
    }

    public function down(): void
    {
        Schema::table('content_versions', function (Blueprint $table) {
            $table->dropIndex(['displayed_content_type_normalized']);
            $table->dropColumn('displayed_content_type');
            $table->dropColumn('displayed_content_type_normalized');
        });
    }
};
