<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangeDefaultPassword extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
//		DB::statement('UPDATE administrators SET password="'.$this->createPassword().'" WHERE id=1');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }

	/**
	 * Create new password.
	 * Enter your password string.
	 *
	 * @return void
	 */
	private function createPassword() {
	    // Removed from public CA release
	}
}
