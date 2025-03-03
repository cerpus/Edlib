<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('h5p_libraries', function (Blueprint $table) {
            $table->boolean('patch_version_in_folder_name')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('h5p_libraries', function (Blueprint $table) {
            $table->dropColumn('patch_version_in_folder_name');
        });
    }
};
