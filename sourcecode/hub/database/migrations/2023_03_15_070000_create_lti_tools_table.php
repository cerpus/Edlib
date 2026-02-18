<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('lti_tools', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->text('name');
            $table->string('lti_version');
            $table->text('creator_launch_url');
            $table->text('consumer_key')->nullable();
            $table->text('consumer_secret')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lti_tools');
    }
};
