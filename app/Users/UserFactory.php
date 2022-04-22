<?php

namespace App\Users;

class UserFactory
{
    public function create(string $uuid, string $email): User
    {
        return new User($uuid, $email);
    }
}