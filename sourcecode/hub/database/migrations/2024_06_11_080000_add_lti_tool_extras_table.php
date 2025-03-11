<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('lti_tool_extras', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->ulid('lti_tool_id');
            $table->text('name');
            $table->text('lti_launch_url');
            $table->boolean('admin')->default(false);

            $table->foreign('lti_tool_id')->references('id')->on('lti_tools');
        });
    }

    public function down(): void
    {
        Schema::drop('lti_tool_extras');
    }
};
