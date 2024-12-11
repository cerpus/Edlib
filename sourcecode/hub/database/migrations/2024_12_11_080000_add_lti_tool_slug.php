<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('lti_tools', function (Blueprint $table) {
            $table->string('slug')->nullable();

            $table->unique(['slug']);
        });

        Schema::table('lti_tool_extras', function (Blueprint $table) {
            $table->string('slug')->nullable();

            $table->unique(['lti_tool_id', 'slug']);
        });

        DB::update('UPDATE lti_tools SET slug = id');
        DB::update('UPDATE lti_tool_extras SET slug = id');

        Schema::table('lti_tools', function (Blueprint $table) {
            $table->string('slug')->nullable(false)->change();
        });

        Schema::table('lti_tool_extras', function (Blueprint $table) {
            $table->string('slug')->nullable(false)->change();
        });
    }

    public function down(): void
    {
        Schema::table('lti_tools', function (Blueprint $table) {
            $table->dropColumn('slug');
        });

        Schema::table('lti_tool_extras', function (Blueprint $table) {
            $table->dropColumn('slug');
        });
    }
};
