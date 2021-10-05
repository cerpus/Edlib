<?php

use Illuminate\Database\Migrations\Migration;

class IncreaseTextFieldSize extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('h5p_contents', function ($table) {
            $table->longText('parameters')->change();
            $table->longText('filtered')->change();
        });

        Schema::table('h5p_contents_user_data', function ($table) {
            $table->longText('data')->change();
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
            $table->text('parameters')->change();
            $table->text('filtered')->change();
        });

        Schema::table('h5p_contents_user_data', function ($table) {
            $table->text('data')->change();
        });
    }
}
