<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('lti_tools', function (Blueprint $table) {
            $table->boolean('supports_destroy')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('lti_tools', function (Blueprint $table) {
            $table->dropColumn('supports_destroy');
        });
    }
};
