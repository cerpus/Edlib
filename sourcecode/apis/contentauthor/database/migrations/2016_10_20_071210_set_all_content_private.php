<?php

use Illuminate\Database\Migrations\Migration;

class SetAllContentPrivate extends Migration
{
    public function up()
    {
        DB::update('update articles set is_private=1');
        DB::update('update h5p_contents set is_private=1');
    }

    public function down()
    {
        // no going back
    }
}
