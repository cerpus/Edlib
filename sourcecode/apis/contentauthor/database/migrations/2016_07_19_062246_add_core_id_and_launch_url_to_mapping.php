<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddCoreIdAndLaunchUrlToMapping extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('ndla_id_mappers', function (Blueprint $table) {
            $table->string('core_id')->nullable()->default(null);
        });

        Schema::table('ndla_id_mappers', function (Blueprint $table) {
            $table->string('launch_url')->nullable()->default(null);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('ndla_id_mappers', function (Blueprint $table) {
            $table->dropColumn('core_id');
        });

        Schema::table('ndla_id_mappers', function (Blueprint $table) {
            $table->dropColumn('launch_url');
        });

    }
}
