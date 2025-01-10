<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('lti_resources', function (Blueprint $table) {
            $table->dropColumn('edit_launch_url');
        });

        Schema::table('lti_tools', function (Blueprint $table) {
            $table->string('edit_mode')->default('replace');
        });
    }

    public function down(): void
    {
        Schema::table('lti_resources', function (Blueprint $table) {
            $table->text('edit_launch_url')->default('https://invalid/');
        });

        Schema::table('lti_tools', function (Blueprint $table) {
            $table->dropColumn('edit_mode');
        });
    }
};
