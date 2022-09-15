<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateAdministratorsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('administrators', function (Blueprint $table) {
            $table->increments('id');
            $table->string('username', 190)->unique();
            $table->string('password', 60);
            $table->string('name');
            $table->string('remember_token', 100)->nullable();
            $table->timestamps();
        });

        Schema::table('administrators', function (Blueprint $table) {
            $admin = new \App\Administrator();
            $admin->username = 'admin';
            $admin->password = md5(time());
            $admin->name = 'SleepingOwl Administrator';
            $admin->save();
        });
    }


    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('administrators');
    }
}
