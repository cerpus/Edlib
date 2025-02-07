<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('h5p_contents_libraries', function (Blueprint $table) {
            $table->index('library_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('h5p_contents_libraries', function (Blueprint $table) {
            $table->dropIndex(['library_id']);
        });
    }
};
