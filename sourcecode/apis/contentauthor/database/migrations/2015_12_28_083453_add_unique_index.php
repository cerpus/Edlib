<?php

use Illuminate\Database\Migrations\Migration;

class AddUniqueIndex extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('cerpus_contents_shares', function ($table) {
            $table->string('email', 100)->nullable()->change();
            $table->unique(['h5p_id', 'email']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('cerpus_contents_shares', function ($table) {
            $table->text('email')->nullable()->change();
            $table->dropUnique(['h5p_id', 'email']);
        });
    }
}
