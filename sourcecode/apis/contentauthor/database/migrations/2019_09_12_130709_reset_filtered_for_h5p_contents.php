<?php

use Illuminate\Database\Migrations\Migration;

class ResetFilteredForH5pContents extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::update("UPDATE h5p_contents SET filtered = '' WHERE updated_at > '2019-08-27'");
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
