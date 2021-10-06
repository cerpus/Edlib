<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateGdprTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
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

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('gdpr_logs');
        Schema::dropIfExists('gdpr_deletion_requests');
    }
}
