<?php

declare(strict_types=1);

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
        // Eloquent has no native support for composite primary keys
        Schema::table('h5p_libraries_libraries', function (Blueprint $table) {
            $table->dropPrimary(['library_id', 'required_library_id']);
        });

        Schema::table('h5p_libraries_libraries', function (Blueprint $table) {
            $table->increments('id')->first();
            $table->unique(['library_id', 'required_library_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('h5p_libraries_libraries', function (Blueprint $table) {
            $table->dropUnique(['library_id', 'required_library_id']);
            $table->dropColumn('id');
        });

        Schema::table('h5p_libraries_libraries', function (Blueprint $table) {
            $table->primary(['library_id', 'required_library_id']);
        });
    }
};
