<?php

use Illuminate\Database\Migrations\Migration;

class ResetFilteredForH5pWithVideos extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::update("UPDATE h5p_contents SET filtered = '' WHERE id IN (SELECT h5p_content_id FROM h5p_contents_video)");
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
