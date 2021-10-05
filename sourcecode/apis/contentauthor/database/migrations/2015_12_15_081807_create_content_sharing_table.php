<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateContentSharingTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cerpus_contents_shares', function(Blueprint $table)
        {
            $table->integer('h5p_id')->unsigned();
            $table->text('email', 65535);
            $table->dateTime('created_at')->default('0000-00-00 00:00:00');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('cerpus_contents_shares');
    }
}
