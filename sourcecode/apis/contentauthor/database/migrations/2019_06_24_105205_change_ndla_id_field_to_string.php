<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangeNdlaIdFieldToString extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('ndla_article_import_statuses', function (Blueprint $table) {
            $table->string('ndla_id')->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Postgres needs an explicit cast to turn a string column into an integer
        if (DB::connection()->getDriverName() === 'pgsql') {
            DB::statement('ALTER TABLE ndla_article_import_statuses ALTER COLUMN ndla_id TYPE integer USING ndla_id::integer');

            return;
        }

        Schema::table('ndla_article_import_statuses', function (Blueprint $table) {
            $table->unsignedInteger('ndla_id')->change();
        });
    }
}
