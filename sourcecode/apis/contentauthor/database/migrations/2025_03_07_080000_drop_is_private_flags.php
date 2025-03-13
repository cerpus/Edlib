<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('articles', function (Blueprint $table) {
            $table->dropColumn('is_private');
        });

        Schema::table('games', function (Blueprint $table) {
            $table->dropColumn('is_private');
        });

        Schema::table('h5p_contents', function (Blueprint $table) {
            $table->dropColumn('is_private');
        });

        Schema::table('links', function (Blueprint $table) {
            $table->dropColumn('is_private');
        });

        Schema::table('question_sets', function (Blueprint $table) {
            $table->dropColumn('is_private');
        });
    }

    public function down(): void
    {
        Schema::table('articles', function (Blueprint $table) {
            $table->boolean('is_private')->default(false);
        });

        Schema::table('games', function (Blueprint $table) {
            $table->boolean('is_private')->default(false);
        });

        Schema::table('h5p_contents', function (Blueprint $table) {
            $table->boolean('is_private')->default(false);
        });

        Schema::table('links', function (Blueprint $table) {
            $table->boolean('is_private')->default(false);
        });

        Schema::table('question_sets', function (Blueprint $table) {
            $table->boolean('is_private')->default(false);
        });
    }
};
