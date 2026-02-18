<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

return new class extends Migration {
    public function up(): void
    {
        Schema::drop('gdpr_deletion_requests');
        Schema::drop('gdpr_logs');
    }

    public function down(): void
    {
        Schema::create('gdpr_deletion_requests', function (Blueprint $table) {
            $table->string('id', 36)->primary();
            $table->mediumText('payload');

            $table->timestamps();

            $table->index('updated_at');
        });

        Schema::create('gdpr_logs', function (Blueprint $table) {
            $table->string('id', 36)->primary();
            $table->string('gdpr_deletion_request_id', 36);
            $table->unsignedInteger('order')->default(1);
            $table->string('status');
            $table->text('message');

            $table->timestamps();

            $table->index(['gdpr_deletion_request_id', 'order'], 'request_id_order_index');
        });
    }
};
