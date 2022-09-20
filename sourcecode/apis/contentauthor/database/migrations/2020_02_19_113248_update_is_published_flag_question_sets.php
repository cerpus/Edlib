<?php

use Illuminate\Database\Migrations\Migration;

class UpdateIsPublishedFlagQuestionSets extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::table('question_sets')
            ->where('is_published', 0)
            ->chunkById(400, function ($contents) {
                foreach ($contents as $content) {
                    DB::table('question_sets')
                        ->where('id', $content->id)
                        ->update(['is_published' => 1]);
                }
            });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
