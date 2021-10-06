<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCollaboratorContextsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('collaborator_contexts', function (Blueprint $table) {
            $table->string('system_id', 50);
            $table->string('context_id', 50);
            $table->string('type', 20);
            $table->uuid('collaborator_id');
            $table->string('content_id', 36);
            $table->timestamp('timestamp');

            $table->index('context_id');
            $table->index('timestamp');
            $table->index(['content_id', 'collaborator_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('collaborator_contexts');
    }
}
