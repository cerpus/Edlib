<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Models\UserLogin;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class CreateAdminUser extends Command
{
    protected $signature = 'edlib:create-admin-user {email}';

    public function handle(): void
    {
        $email = $this->argument('email');
        assert(is_string($email));

        $password = $this->secret('Enter a password for the user');
        assert(is_string($password));

        DB::transaction(function () use ($email, $password) {
            $user = new User();
            $user->name = $email;
            $user->admin = true;
            $user->save();

            $login = new UserLogin();
            $login->email = $email;
            $login->password = Hash::make($password);
            $login->user()->associate($user);
            $login->save();
        });

        $this->info('The user was created');
    }
}
