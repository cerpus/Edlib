<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddVersionToH5PContent extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('h5p_contents', function (Blueprint $table) {
            $table->string('version_id')->nullable()->default(null);
        });

        Schema::table('h5p_contents', function (Blueprint $table) {
            $table->index("version_id");
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
            $table->dropIndex(["version_id"]);
        });

        Schema::table('h5p_contents', function (Blueprint $table) {
            $table->dropColumn("version_id");
        });
    }
}
