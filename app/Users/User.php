<?php

namespace App\Users;

use Illuminate\Contracts\Auth\Authenticatable;

class User implements Authenticatable
{
    public const PROPERTY_UUID  = 'uuid';
    public const PROPERTY_EMAIL = 'email';

    private ?string $authToken;

    private ?string $refreshToken;

    public function __construct(private string $uuid, private string $email)
    {
        $this->authToken = null;
        $this->refreshToken = null;
    }

    public function getUuid(): string
    {
        return $this->uuid;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setAuthToken(?string $authToken): self
    {
        $this->authToken = $authToken;

        return $this;
    }

    public function getAuthToken(): ?string
    {
        return $this->authToken;
    }

    public function setRefreshToken(?string $refreshToken): self
    {
        $this->refreshToken = $refreshToken;

        return $this;
    }

    public function getRefreshToken(): ?string
    {
        return $this->refreshToken;
    }

    public function getAuthIdentifierName(): string
    {
        return self::PROPERTY_UUID;
    }

    public function getAuthIdentifier(): string
    {
        return $this->getUuid();
    }

    public function getAuthPassword(): string
    {
        // Not used
        return '';
    }

    public function getRememberToken()
    {
        // Not used
        return '';
    }

    public function setRememberToken($value): void
    {
        // Not used
    }

    public function getRememberTokenName(): string
    {
        // Not used
        return '';
    }
}