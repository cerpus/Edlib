<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddRequestIdToH5pFile extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('h5p_files', function (Blueprint $table) {
            $table->string('requestId')->nullable()->default(null);
            $table->text('params')->nullable()->default(null);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('h5p_files', function (Blueprint $table) {
            $table->dropColumn(["requestId", "params"]);
        });
    }
}
