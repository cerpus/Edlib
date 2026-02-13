<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('contents', function (Blueprint $table) {
            $table->dropIndex(['edlib2_usage_id']);
            $table->dropColumn('edlib2_usage_id');
        });

        Schema::create('content_edlib2_usages', function (Blueprint $table) {
            $table->bigIncrements('id')->primary();
            $table->ulid('content_id');
            $table->uuid('edlib2_usage_id');

            $table->index(['edlib2_usage_id']);
            $table->unique(['edlib2_usage_id']);
            $table->foreign('content_id')->references('id')->on('contents');
        });
    }

    public function down(): void
    {
        Schema::drop('content_edlib2_usages');

        Schema::table('contents', function (Blueprint $table) {
            $table->uuid('edlib2_usage_id')->nullable();
            $table->index(['edlib2_usage_id']);
            $table->unique(['edlib2_usage_id']);
        });
    }
};
