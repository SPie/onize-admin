<?php

namespace App\Auth;

use App\Users\User;
use Illuminate\Contracts\Auth\StatefulGuard;

class AuthManager
{
    public function __construct(private readonly StatefulGuard $guard)
    {}

    public function loginUser(User $user): self
    {
        $this->guard->login($user);

        return $this;
    }
}
