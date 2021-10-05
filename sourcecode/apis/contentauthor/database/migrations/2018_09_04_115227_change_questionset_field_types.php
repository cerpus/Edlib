<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangeQuestionsetFieldTypes extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('question_sets', function (Blueprint $table) {
            $table->text('title')->change(); // It's the title...people must show some constraint! 64K
        });

        Schema::table('question_set_questions', function (Blueprint $table) {
            $table->mediumText('question_text')->change(); // Taking into account HTML + MathML markup etc. 16MB
        });

        Schema::table('question_set_question_answers', function (Blueprint $table) {
            $table->mediumText('answer_text')->change(); // Taking into account HTML + MathML markup etc. 16MB
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('question_sets', function (Blueprint $table) {
            $table->string('title')->change();
        });

        Schema::table('question_set_questions', function (Blueprint $table) {
            $table->string('question_text')->change();
        });

        Schema::table('question_set_question_answers', function (Blueprint $table) {
            $table->string('answer_text')->change();
        });
    }
}
