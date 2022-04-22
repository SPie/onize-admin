<?php

namespace Tests\Unit\Auth;

use App\Auth\AuthManager;
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
}