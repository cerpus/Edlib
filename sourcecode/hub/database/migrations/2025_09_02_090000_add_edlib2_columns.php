<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('contents', function (Blueprint $table) {
            $table->uuid('edlib2_id')->nullable();
            $table->index(['edlib2_id']);
            $table->unique(['edlib2_id']);

            $table->uuid('edlib2_usage_id')->nullable();
            $table->index(['edlib2_usage_id']);
            $table->unique(['edlib2_usage_id']);
        });
    }

    public function down(): void
    {
        Schema::table('contents', function (Blueprint $table) {
            $table->dropIndex(['edlib2_id']);
            $table->dropColumn('edlib2_id');

            $table->dropIndex(['edlib2_usage_id']);
            $table->dropColumn('edlib2_usage_id');
        });
    }
};
