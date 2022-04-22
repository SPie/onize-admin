<?php

namespace Tests\Helpers;

use App\Auth\AuthManager;
use App\Auth\TokenStore;
use App\Users\User;
use Mockery as m;
use Mockery\CompositeExpectation;
use Mockery\MockInterface;
use Mockery\VerificationDirector;

trait AuthHelper
{
    /**
     * @return AuthManager|MockInterface
     */
    private function createAuthManager(): AuthManager
    {
        return m::spy(AuthManager::class);
    }

    private function assertAuthManagerLoginUser(MockInterface $authManager, User $user): VerificationDirector
    {
        return $authManager
            ->shouldHaveReceived('loginUser')
            ->with($user)
            ->once();
    }

    /**
     * @return TokenStore|MockInterface
     */
    private function createTokenStore(): TokenStore
    {
        return m::spy(TokenStore::class);
    }

    private function assertTokenStoreStoreTokens(MockInterface $tokenStore, string $authToken, ?string $refreshToken): VerificationDirector
    {
        return $tokenStore
            ->shouldHaveReceived('storeTokens')
            ->with($authToken, $refreshToken)
            ->once();
    }
}