<?php

use Illuminate\Database\Migrations\Migration;

class UpdateUserId extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('h5p_contents', function ($table) {
            $table->string('user_id', 36)->change();
        });
        Schema::table('h5p_contents_user_data', function ($table) {
            $table->string('user_id', 36)->change();
        });
        Schema::table('h5p_results', function ($table) {
            $table->string('user_id', 36)->change();
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
            foreach (['h5p_contents', 'h5p_contents_user_data', 'h5p_results'] as $tableName) {
                DB::statement("ALTER TABLE $tableName ALTER COLUMN user_id TYPE integer USING user_id::integer");
            }

            return;
        }

        Schema::table('h5p_contents', function ($table) {
            $table->integer('user_id')->unsigned()->change();
        });
        Schema::table('h5p_contents_user_data', function ($table) {
            $table->integer('user_id')->unsigned()->change();
        });
        Schema::table('h5p_results', function ($table) {
            $table->integer('user_id')->unsigned()->change();
        });
    }
}
