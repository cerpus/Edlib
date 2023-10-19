<?php

declare(strict_types=1);

namespace Tests\Stub;

use Laravel\Socialite\Contracts\User;

class SocialiteUser implements User
{
    public function getId(): string
    {
        return '123';
    }

    public function getNickname(): null
    {
        return null;
    }

    public function getName(): string
    {
        return 'Emma';
    }

    public function getEmail(): string
    {
        return 'emma@edlib.test';
    }

    public function getAvatar(): null
    {
        return null;
    }
}
