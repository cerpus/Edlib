<?php

use Illuminate\Database\Migrations\Migration;

class UpdateIsPublishedFlagGames extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::table('games')
            ->where('is_published', 0)
            ->chunkById(400, function ($contents) {
                foreach ($contents as $content) {
                    DB::table('games')
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
