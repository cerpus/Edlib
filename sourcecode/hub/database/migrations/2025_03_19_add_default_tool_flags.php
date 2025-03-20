<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('lti_tools', function (Blueprint $table) {
            $table->boolean('default_published')->default(false);
            $table->boolean('default_shared')->default(false);
        });
    }

    public function down(): void
    {
        Schema::table('lti_tools', function (Blueprint $table) {
            $table->dropColumn('default_published');
            $table->dropColumn('default_shared');
        });
    }
};
