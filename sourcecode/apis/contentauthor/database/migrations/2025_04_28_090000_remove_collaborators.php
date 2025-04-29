<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::drop('article_collaborators');
        Schema::drop('cerpus_contents_shares');
        Schema::drop('collaborators');
        Schema::drop('collaborator_contexts');
    }

    public function down(): void
    {
        Schema::create('article_collaborators', function (Blueprint $table) {
            $table->increments('id');
            $table->string('article_id', 36)->index();
            $table->string('email');
            $table->timestamps();

            $table->foreign('article_id')->references('id')->on('articles');
        });

        Schema::create('cerpus_contents_shares', function (Blueprint $table) {
            $table->integer('h5p_id')->unsigned();
            $table->string('email', 100);
            $table->dateTime('created_at')->default('0000-00-00 00:00:00');

            $table->unique(['h5p_id', 'email']);
        });

        Schema::create('collaborators', function (Blueprint $table) {
            $table->increments('id');
            $table->string('email');
            $table->uuid('collaboratable_id');
            $table->string('collaboratable_type');
            $table->timestamps();

            $table->index(['collaboratable_id', 'collaboratable_type']);
        });

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
};
