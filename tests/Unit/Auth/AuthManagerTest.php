<?php

namespace Tests\Unit\Auth;

use App\Auth\AuthManager;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Contracts\Auth\StatefulGuard;
use Tests\Helpers\LaravelHelpers;
use Tests\Helpers\UsersHelper;
use Tests\TestCase;

final class AuthManagerTest extends TestCase
{
    use LaravelHelpers;
    use UsersHelper;

    private function getAuthManager(StatefulGuard $guard = null): AuthManager
    {
        return new AuthManager($guard ?: $this->createStatefulGuard());
    }

    public function testLoginUser(): void
    {
        $user = $this->createUser();
        $guard = $this->createStatefulGuard();

        $this->getAuthManager($guard)->loginUser($user);

        $this->assertStatefulGuardLogin($guard, $user);
    }

    private function setUpLoginTest(bool $withValidCredentials = true): array
    {
        $email = $this->getFaker()->safeEmail;
        $password = $this->getFaker()->password(12);
        $user = $this->createUser();
        $guard = $this->createStatefulGuard();
        $this->mockStatefulGuardAttempt($guard, $withValidCredentials, ['email' => $email, 'password' => $password]);
        $this->mockStatefulGuardUser($guard, $user);
        $authManager = $this->getAuthManager($guard);

        return [$authManager, $email, $password, $user];
    }

    public function testLogin(): void
    {
        /** @var AuthManager $authManager */
        [$authManager, $email, $password, $user] = $this->setUpLoginTest();

        $this->assertEquals($user, $authManager->login($email, $password));
    }

    public function testLoginWithInvalidCredentials(): void
    {
        /** @var AuthManager $authManager */
        [$authManager, $email, $password] = $this->setUpLoginTest(withValidCredentials: false);

        $this->expectException(AuthenticationException::class);

        $authManager->login($email, $password);
    }
}