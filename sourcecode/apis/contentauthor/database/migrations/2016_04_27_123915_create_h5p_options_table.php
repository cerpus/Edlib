<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateH5pOptionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('h5p_options')) {
            Schema::create('h5p_options', function(Blueprint $table)
            {
                $table->bigIncrements('option_id')->unsigned();
                $table->string('option_name', 191)->nullable()->default(null);
                $table->longText('option_value');
                $table->string('autoload', 20);
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::hasTable('h5p_options')) {
            Schema::drop('h5p_options');
        }
    }
}
