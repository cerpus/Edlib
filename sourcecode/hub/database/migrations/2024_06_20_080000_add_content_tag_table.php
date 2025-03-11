<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('content_tag', function (Blueprint $table) {
            $table->ulid('content_id');
            $table->uuid('tag_id');
            $table->text('verbatim_name')->nullable();
        });
    }

    public function down(): void
    {
        Schema::drop('content_tag');
    }
};
