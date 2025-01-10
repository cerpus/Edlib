<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('lti_platforms', function (Blueprint $table) {
            $table->text('name')->unique(false)->change();
        });
    }

    public function down(): void
    {
        Schema::table('lti_platforms', function (Blueprint $table) {
            $table->text('name')->unique()->change();
        });
    }
};
