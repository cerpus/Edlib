<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddVersioningToGame extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('games', function (Blueprint $table) {
            $table->string('version_id')->nullable()->default(null);
        });
        Schema::table('games', function (Blueprint $table) {
            $table->index('version_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('games', function (Blueprint $table) {
            $table->dropIndex(['version_id']);
        });
        Schema::table('games', function (Blueprint $table) {
            $table->dropColumn('version_id');
        });
    }
}
