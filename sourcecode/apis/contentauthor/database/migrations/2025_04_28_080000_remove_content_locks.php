<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

return new class extends Migration {
    public function up(): void
    {
        Schema::drop('content_locks');
    }

    public function down(): void
    {
        Schema::create('content_locks', function (Blueprint $table) {
            $table->uuid('content_id')->index();
            $table->uuid('auth_id');
            $table->string('email')->nullable();
            $table->string('name')->nullable();
            $table->timestamps();

            $table->index(['content_id', 'auth_id']);
            $table->index(['content_id', 'auth_id', 'created_at']);
        });

        Schema::table('content_locks', function (Blueprint $table) {
            $table->primary('content_id');
        });

        Schema::table('content_locks', function (Blueprint $table) {
            $table->index('updated_at');
        });
    }
};
