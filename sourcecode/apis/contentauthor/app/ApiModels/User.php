<?php

namespace App\ApiModels;

class User
{
    private string $id;
    private ?string $firstName;
    private ?string $lastName;
    private ?string $email;

    public function __construct(string $id, ?string $firstName, ?string $lastName, ?string $email)
    {
        $this->id = $id;
        $this->firstName = $firstName;
        $this->lastName = $lastName;
        $this->email = $email;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getFirstName($default = null): ?string
    {
        return $this->firstName ?? $default;
    }

    public function getLastName($default = null): ?string
    {
        return $this->lastName ?? $default;
    }

    public function getEmail($default = null): ?string
    {
        return $this->email ?? $default;
    }
}
