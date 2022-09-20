<?php

use Illuminate\Database\Migrations\Migration;

class SetAdminPassword extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
//        DB::statement('UPDATE administrators SET password="'.$this->createPassword().'" WHERE username="admin"');
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
     * @return string
     */
    private function createPassword()
    {
        // Removed from public CA release
    }
}
