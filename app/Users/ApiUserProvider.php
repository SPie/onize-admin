<?php

namespace App\Users;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Auth\UserProvider;

final class ApiUserProvider implements UserProvider
{
    private ?User $user;

    public function __construct(private readonly UserManager $userManager)
    {
        $this->user = null;
    }

    public function retrieveById($identifier): ?Authenticatable
    {
        if (!$this->user) {
            $this->user = $this->userManager->authenticatedUser();
        }

        return ($this->user && $this->user->getAuthIdentifier() === $identifier) ? $this->user : null;
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