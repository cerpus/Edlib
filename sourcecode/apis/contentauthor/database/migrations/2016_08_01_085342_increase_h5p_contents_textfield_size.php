<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class IncreaseH5pContentsTextfieldSize extends Migration
{
    CONST longTextLength = 4294967295;
    CONST textLength = 65535;
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('h5p_contents', function ($table) {
            $table->string('parameters', self::longTextLength)->change();
            $table->string('filtered', self::longTextLength)->change();
        });

        Schema::table('h5p_contents_user_data', function ($table) {
            $table->string('data', self::longTextLength)->change();
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
            $table->string('parameters', self::textLength)->change();
            $table->string('filtered', self::textLength)->change();
        });

        Schema::table('h5p_contents_user_data', function ($table) {
            $table->string('data', self::textLength)->change();
        });
    }
}
