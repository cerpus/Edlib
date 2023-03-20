<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('resources', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('lti_tool_id');
            $table->text('title');
            $table->text('title_html')->nullable();
            $table->boolean('published');
            $table->timestampTz('created_at');

            $table->foreign('lti_tool_id')->references('id')->on('lti_tools');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('resources');
    }
};
