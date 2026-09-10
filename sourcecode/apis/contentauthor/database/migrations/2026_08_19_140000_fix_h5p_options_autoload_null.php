<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Postgres, unlike MySQL, refuses to read rows where a NOT NULL
        // column ended up NULL (e.g. rows inserted before the column had a
        // default). Normalize those to the column's intended default.
        DB::table('h5p_options')
            ->whereNull('autoload')
            ->update(['autoload' => 0]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Not reversible: original NULL rows can't be distinguished from
        // rows that legitimately had autoload = 0.
    }
};
