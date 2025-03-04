<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('articles', function (Blueprint $table) {
            $table->dropColumn('is_published');
        });

        Schema::table('games', function (Blueprint $table) {
            $table->dropColumn('is_published');
        });

        Schema::table('h5p_contents', function (Blueprint $table) {
            $table->dropColumn('is_published');
        });

        Schema::table('links', function (Blueprint $table) {
            $table->dropColumn('is_published');
        });

        Schema::table('question_sets', function (Blueprint $table) {
            $table->dropColumn('is_published');
        });
    }

    public function down(): void
    {
        Schema::table('articles', function (Blueprint $table) {
            $table->boolean('is_published')->default(false);
        });

        Schema::table('games', function (Blueprint $table) {
            $table->boolean('is_published')->default(false);
        });

        Schema::table('h5p_contents', function (Blueprint $table) {
            $table->boolean('is_published')->default(false);
        });

        Schema::table('links', function (Blueprint $table) {
            $table->boolean('is_published')->default(false);
        });

        Schema::table('question_sets', function (Blueprint $table) {
            $table->boolean('is_published')->default(false);
        });
    }
};
