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

        if ($this->user && $this->user->getAuthIdentifier() !== $identifier) {
            $this->user = null;

            return null;
        }

        return $this->user;
    }

    public function retrieveByToken($identifier, $token): ?Authenticatable
    {
        // Not used
    }

    public function updateRememberToken(Authenticatable $user, $token): void
    {
        // Not used
    }

    public function retrieveByCredentials(array $credentials): ?User
    {
        if (empty($credentials[User::PROPERTY_EMAIL]) || empty($credentials[User::PROPERTY_PASSWORD])) {
            return null;
        }

        $this->user = $this->userManager->login($credentials[User::PROPERTY_EMAIL], $credentials[User::PROPERTY_PASSWORD]);

        return $this->user;
    }

    public function validateCredentials(Authenticatable|User $user, array $credentials): bool
    {
        // Credentials are checked by retrieveByCredentials
        return $this->user === $user && $user->getEmail() === $credentials[User::PROPERTY_EMAIL];
    }
}