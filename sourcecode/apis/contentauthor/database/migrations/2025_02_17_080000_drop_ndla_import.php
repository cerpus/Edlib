<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::drop('ndla_article_ids');
        Schema::drop('ndla_article_import_statuses');
    }

    public function down(): void
    {
        Schema::create('ndla_article_ids', function (Blueprint $table) {
            $table->bigIncrements('id')->primary();
            $table->string('title');
            $table->string('language', 2);
            $table->string('type');
            $table->longText('json');
            $table->timestamps();

            $table->index('title');
        });

        Schema::create('ndla_article_import_statuses', function (Blueprint $table) {
            $table->increments('id')->primary();
            $table->string('ndla_id');
            $table->text('message');
            $table->timestamps();
            $table->string('import_id')->nullable();
            $table->smallInteger('log_level')->unsigned()->default(100);

            $table->index('import_id');
            $table->index('ndla_id');
        });
    }
};
