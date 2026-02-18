<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('lti_resources', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->ulid('lti_tool_id');
            $table->text('view_launch_url');
            $table->text('edit_launch_url');
            $table->text('title');
            $table->text('title_html')->nullable();
            $table->timestampTz('created_at');

            $table->foreign('lti_tool_id')->references('id')->on('lti_tools');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lti_resources');
    }
};
