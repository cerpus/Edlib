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
        Schema::table('h5p_libraries_languages', function (Blueprint $table) {
            $table->dropPrimary(['library_id', 'language_code']);
        });

        Schema::table('h5p_libraries_languages', function (Blueprint $table) {
            $table->increments('id')->first();
            $table->unique(['library_id', 'language_code']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('h5p_libraries_languages', function (Blueprint $table) {
            $table->dropUnique(['library_id', 'language_code']);
            $table->dropColumn('id');
        });

        Schema::table('h5p_libraries_languages', function (Blueprint $table) {
            $table->primary(['library_id', 'language_code']);
        });
    }
};
