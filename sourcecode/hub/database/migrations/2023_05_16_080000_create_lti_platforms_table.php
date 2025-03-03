<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('lti_platforms', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->text('name')->unique();
            $table->string('key')->unique();
            $table->string('secret');
            $table->timestampsTz();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lti_platforms');
    }
};
