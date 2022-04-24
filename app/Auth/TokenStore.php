<?php

namespace App\Auth;

use Illuminate\Contracts\Session\Session;

class TokenStore
{
    public function __construct(private readonly Session $session)
    {}

    public function storeTokens(string $authToken, string $refreshToken = null): self
    {
        $this->session->put([
            'authToken'    => $authToken,
            'refreshToken' => $refreshToken,
        ]);

        return $this;
    }

    public function getAuthToken(): ?string
    {
        // TODO
    }

    public function getRefreshToken(): ?string
    {
        // TODO
    }
}