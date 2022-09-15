<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateLearningPaths extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('learning_paths', function (Blueprint $table) {
            $table->bigInteger('id')->primary();
            $table->string('title');
            $table->longText('json');

            $table->timestamps();
        });

        Schema::create('learning_path_steps', function (Blueprint $table) {
            $table->bigInteger('id')->primary();
            $table->bigInteger('learning_path_id')->index();
            $table->string('title');
            $table->unsignedInteger('order');
            $table->longText('json');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('learning_path_steps');
        Schema::dropIfExists('learning_paths');
    }
}
