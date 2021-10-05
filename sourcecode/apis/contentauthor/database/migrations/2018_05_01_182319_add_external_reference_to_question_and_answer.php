<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddExternalReferenceToQuestionAndAnswer extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('question_sets', function (Blueprint $table) {
            $table->unsignedTinyInteger('is_private')->default(1);
        });

        Schema::table('question_set_questions', function (Blueprint $table) {
            $table->string('external_reference')->nullable()->default(null);
            $table->unsignedTinyInteger('is_private')->default(1);
        });

        Schema::table('question_set_question_answers', function (Blueprint $table) {
            $table->string('external_reference')->nullable()->default(null);
            $table->unsignedTinyInteger('is_private')->default(1);
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
            $table->dropColumn('is_private');
        });

        Schema::table('question_set_questions', function (Blueprint $table) {
            $table->dropColumn('external_reference', 'is_private');
        });

        Schema::table('question_set_question_answers', function (Blueprint $table) {
            $table->dropColumn('external_reference', 'is_private');
        });
    }
}
