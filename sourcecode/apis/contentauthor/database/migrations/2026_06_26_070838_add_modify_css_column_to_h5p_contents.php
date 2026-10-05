<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('h5p_contents', function (Blueprint $table) {
            $table->boolean('modify_css')->default(false);
        });
    }

    public function down(): void
    {
        Schema::table('h5p_contents', function (Blueprint $table) {
            $table->dropColumn('modify_css');
        });
    }
};
