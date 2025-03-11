<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('content_versions', function (Blueprint $table) {
            $table->decimal('min_score')->default('0');
            $table->decimal('max_score')->default('0');
        });

        DB::statement('ALTER TABLE content_versions ADD CONSTRAINT score_check CHECK (max_score >= min_score)');
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE content_versions DROP CONSTRAINT score_check');

        Schema::table('content_versions', function (Blueprint $table) {
            $table->dropColumn('min_score');
            $table->dropColumn('max_score');
        });
    }
};
