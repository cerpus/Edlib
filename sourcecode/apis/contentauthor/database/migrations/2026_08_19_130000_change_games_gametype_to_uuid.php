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
        // Databases created before the "games" table migration was fixed to use
        // a uuid column for "gametype" will still have it as a varchar(255).
        // Skip on databases where the column is already correct (e.g. fresh installs).
        if (Schema::getColumnType('games', 'gametype') !== 'varchar') {
            return;
        }

        Schema::table('games', function (Blueprint $table) {
            $table->dropForeign(['gametype']);
        });

        Schema::table('games', function (Blueprint $table) {
            $table->uuid('gametype')->change();
        });

        Schema::table('games', function (Blueprint $table) {
            $table->foreign('gametype')->references('id')->on('gametypes');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Nothing to revert: "gametype" should be a uuid on every database, and
        // changing it back to a varchar breaks the foreign key on Postgres.
    }
};
