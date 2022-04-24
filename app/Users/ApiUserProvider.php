<?php

namespace App\Users;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Auth\UserProvider;

final class ApiUserProvider implements UserProvider
{
    public function __construct(private readonly UserManager $userManager)
    {}

    public function retrieveById($identifier): ?Authenticatable
    {
        $user = $this->userManager->authenticatedUser();

        return ($user && $user->getAuthIdentifier() === $identifier) ? $user : null;
    }

    public function retrieveByToken($identifier, $token): ?Authenticatable
    {
        // Not used
    }

    public function updateRememberToken(Authenticatable $user, $token): void
    {
        // Not used
    }

    public function retrieveByCredentials(array $credentials): ?Authenticatable
    {
        // TODO: Implement retrieveByCredentials() method.
    }

    public function validateCredentials(Authenticatable $user, array $credentials): bool
    {
        // TODO: Implement validateCredentials() method.
    }
}