<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::dropIfExists('h5p_refid_support');
    }

    public function down(): void
    {
        Schema::create('h5p_refid_support', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->timestamps();
            $table->integer('content_id');
            $table->string('title');
            $table->boolean('processed')->default(0);
            $table->boolean('istarget')->default(0);
            $table->unique(['content_id']);
        });
    }
};
