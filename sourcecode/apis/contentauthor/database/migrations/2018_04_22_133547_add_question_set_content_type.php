<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddQuestionSetContentType extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('question_sets', function (Blueprint $table) {
            $table->uuid('id');
            $table->string('title');
            $table->string('language_code');
            $table->string('owner');
            $table->string('external_reference')->nullable()->default(null);

            $table->timestamps();

            $table->primary('id');
        });

        Schema::create('question_set_questions', function (Blueprint $table) {
            $table->uuid('id');
            $table->uuid('question_set_id');
            $table->text('question_text');
            $table->text('image')->nullable()->default(null);
            $table->integer('order')->default(0);

            $table->timestamps();

            $table->primary('id');
            $table->foreign('question_set_id')->references('id')->on('question_sets');
        });

        Schema::create('question_set_question_answers', function (Blueprint $table) {
            $table->uuid('id');
            $table->uuid('question_id');
            $table->text('answer_text');
            $table->boolean('correct')->default(true);
            $table->text('image')->nullable()->default(null);
            $table->integer('order')->default(0);

            $table->timestamps();

            $table->primary('id');
            $table->foreign('question_id')->references('id')->on('question_set_questions');
        });

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('question_set_question_answers');
        Schema::dropIfExists('question_set_questions');
        Schema::dropIfExists('question_sets');
    }
}
