<?php

use Illuminate\Database\Migrations\Migration;

class AddIndexLibraryId extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('h5p_contents', function ($table) {
            $table->index('library_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('h5p_contents', function ($table) {
            $table->dropIndex('h5p_contents_library_id_index');
        });
    }
}
