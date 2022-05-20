<?php

namespace App\Auth;

use App\Users\User;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Auth\StatefulGuard;

class AuthManager
{
    public function __construct(private readonly StatefulGuard $guard)
    {}

    public function login(string $email, string $password): User|Authenticatable
    {
        if (!$this->guard->attempt([User::PROPERTY_EMAIL => $email, User::PROPERTY_PASSWORD => $password])) {
            throw new AuthenticationException();
        }

        return $this->guard->user();
    }

    public function loginUser(User $user): self
    {
        $this->guard->login($user);

        return $this;
    }
}
