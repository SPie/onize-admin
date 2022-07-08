<?php

namespace Tests\Helpers;

use App\Users\User;
use App\Users\UserFactory;
use App\Users\UserManager;
use Mockery as m;
use Mockery\CompositeExpectation;
use Mockery\MockInterface;
use Mockery\VerificationDirector;

trait UsersHelper
{
    /**
     * @return UserManager|MockInterface
     */
    private function createUserManager(): UserManager
    {
        return m::spy(UserManager::class);
    }

    /**
     * @param User|\Exception $user
     */
    private function mockUserManagerRegister(MockInterface $userManager, $user, string $email, string $password): CompositeExpectation
    {
        return $userManager
            ->shouldReceive('register')
            ->with($email, $password)
            ->andThrow($user);
    }

    private function mockUserManagerAuthenticatedUser(MockInterface $userManager, ?User $user): CompositeExpectation
    {
        return $userManager
            ->shouldReceive('authenticatedUser')
            ->andReturn($user);
    }

    private function mockUserManagerLogin(MockInterface $userManager, ?User $user, string $email, string $password): CompositeExpectation
    {
        return $userManager
            ->shouldReceive('login')
            ->with($email, $password)
            ->andReturn($user);
    }

    private function mockUserMangerEditProfile(MockInterface $userManager, $user, string $email): CompositeExpectation
    {
        return $userManager
            ->shouldReceive('editProfile')
            ->with($email)
            ->andThrow($user);
    }

    private function assertUserMangerEditProfile(MockInterface $userManager, string $email): VerificationDirector
    {
        return $userManager
            ->shouldHaveReceived('editProfile')
            ->with($email)
            ->once();
    }

    private function assertUserManagerEditPassword(MockInterface $userManager, string $currentPassword, string $password): VerificationDirector
    {
        return $userManager
            ->shouldHaveReceived('editPassword')
            ->with($currentPassword, $password)
            ->once();
    }

    private function createUser(string $uuid = null, string $email = null): User
    {
        return new User(
            $uuid ?: $this->getFaker()->uuid,
            $email ?: $this->getFaker()->safeEmail
        );
    }

    /**
     * @return UserFactory|MockInterface
     */
    private function createUserFactory(): UserFactory
    {
        return m::spy(UserFactory::class);
    }

    /**
     * @param User|\Exception $user
     */
    private function mockUserFactoryCreate(MockInterface $userFactory, $user, string $uuid, string $email): CompositeExpectation
    {
        return $userFactory
            ->shouldReceive('create')
            ->with($uuid, $email)
            ->andThrow($user);
    }
}
