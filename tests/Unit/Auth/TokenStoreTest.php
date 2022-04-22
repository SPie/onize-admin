<?php

namespace Tests\Unit\Auth;

use App\Auth\TokenStore;
use Illuminate\Contracts\Session\Session;
use Tests\Helpers\LaravelHelpers;
use Tests\TestCase;

final class TokenStoreTest extends TestCase
{
    use LaravelHelpers;

    private function getTokenStore(Session $session = null): TokenStore
    {
        return new TokenStore($session ?: $this->createSession());
    }

    public function testStoreTokens(): void
    {
        $authToken = $this->getFaker()->word;
        $refreshToken = $this->getFaker()->word;
        $session = $this->createSession();
        $tokenStore = $this->getTokenStore($session);

        $this->assertEquals($tokenStore, $tokenStore->storeTokens($authToken, $refreshToken));
        $this->assertSessionPut($session, ['authToken' => $authToken, 'refreshToken' => $refreshToken]);
    }

    public function testStoreTokensWithoutRefreshToken(): void
    {
        $authToken = $this->getFaker()->word;
        $session = $this->createSession();
        $tokenStore = $this->getTokenStore($session);

        $tokenStore->storeTokens($authToken);

        $this->assertSessionPut($session, ['authToken' => $authToken, 'refreshToken' => null]);
    }
}