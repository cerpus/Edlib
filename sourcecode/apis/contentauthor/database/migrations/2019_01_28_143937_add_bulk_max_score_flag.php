<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddBulkMaxScoreFlag extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('h5p_contents', function (Blueprint $table) {
            $table->tinyInteger('bulk_calculated')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('h5p_contents', function (Blueprint $table) {
            $table->dropColumn('bulk_calculated');
        });
    }
}
