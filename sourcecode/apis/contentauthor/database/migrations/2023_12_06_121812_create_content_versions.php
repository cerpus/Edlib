<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('content_versions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('content_id', 36)->nullable(false)->index();
            $table->string('content_type', 15)->nullable();
            $table->uuid('parent_id')->nullable()->default(null);
            $table->timestamp('created_at', 3)->nullable();
            $table->string('version_purpose', 50);
            $table->string('user_id')->nullable();
            $table->boolean('linear_versioning')->nullable(false)->default(false);
        });

        Schema::table('content_versions', function (Blueprint $table) {
            $table->foreign('parent_id')->references('id')->on('content_versions');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('content_versions');
    }
};
