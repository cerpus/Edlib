<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateH5pResultsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('h5p_results', function(Blueprint $table)
		{
			$table->increments('id');
			$table->integer('content_id')->unsigned();
			$table->integer('user_id')->unsigned();
			$table->integer('score')->unsigned();
			$table->integer('max_score')->unsigned();
			$table->integer('opened')->unsigned();
			$table->integer('finished')->unsigned();
			$table->integer('time')->unsigned();
			$table->index(['content_id','user_id'], 'content_user');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('h5p_results');
	}

}
