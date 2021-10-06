<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateIsPublishedFlagArticles extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::table('articles')
            ->where('is_published', 0)
            ->chunkById(400, function ($contents) {
                foreach ($contents as $content) {
                    DB::table('articles')
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
