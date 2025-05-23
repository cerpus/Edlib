<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('lti_tools', function (Blueprint $table) {
            $table->dropUnique(['consumer_key']);
        });
    }

    public function down(): void
    {
        Schema::table('lti_tools', function (Blueprint $table) {
            $table->unique('consumer_key');
        });
    }
};
