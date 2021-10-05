<?php

use Illuminate\Database\Migrations\Migration;

class EnsureSleepingAdminUser extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        /*
         * SleepingOwl is removed so this migration is not applicaple
         *
        $user = DB::table('administrators')->find(1);
        // This messes up the tests as this seeder no longer exists i laravelRUS/SleepingOwl.
        // Commented out by Odd-Arne Johansen as part of migrating to Laravel 5.2 and PHP 7
        */
        /*
        if( is_null($user)){
            \Illuminate\Support\Facades\Artisan::call('db:seed', [
                '--class' => 'SleepingOwl\\AdminAuth\\Database\\Seeders\\AdministratorsTableSeeder'
            ]);
            DB::table("administrators")
                ->where("id", 1)
                ->update([
                    "password" => // Removed from public CA release
                ]);
        }
        */
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
}
