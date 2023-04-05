<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
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

        $user = new User();
        $user->name = $email;
        $user->email = $email;
        $user->password = Hash::make($password);
        $user->admin = true;
        $user->save();

        $this->info('The user was created');
    }
}
