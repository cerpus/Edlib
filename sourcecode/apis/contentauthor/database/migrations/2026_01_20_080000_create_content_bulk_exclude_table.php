<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    public function up()
    {
        /**
         * Exclude content from being changed by bulk tools, e.g. Update content type translations
         */
        Schema::create('content_bulk_excludes', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->unsignedInteger('content_id')->nullable(false)->index();
            $table->timestamp('created_at', 3)->nullable();
            $table->string('user_id');
            $table->string('exclude_from', 128)->nullable(false)->index();

            $table->foreign('content_id')->references('id')->on('h5p_contents')->onDelete('cascade');
            $table->unique(['content_id', 'exclude_from']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('content_bulk_excludes');
    }
};
