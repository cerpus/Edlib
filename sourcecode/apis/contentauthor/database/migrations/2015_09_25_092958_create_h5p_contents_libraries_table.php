<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateH5pContentsLibrariesTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('h5p_contents_libraries', function(Blueprint $table)
		{
			$table->integer('content_id')->unsigned();
			$table->integer('library_id')->unsigned();
			$table->string('dependency_type', 31);
			$table->smallInteger('weight')->unsigned()->default(0);
			$table->boolean('drop_css');
			$table->primary(['content_id','library_id','dependency_type'], 'hcl_pk');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('h5p_contents_libraries');
	}

}
