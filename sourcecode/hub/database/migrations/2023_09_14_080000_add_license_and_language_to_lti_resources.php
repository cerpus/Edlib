<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('lti_resources', function (Blueprint $table) {
            $table->string('language_iso_639_3')->default('und');
            $table->string('license')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('lti_resources', function (Blueprint $table) {
            $table->dropColumn('language_iso_639_3');
            $table->dropColumn('license');
        });
    }
};
