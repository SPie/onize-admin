<?php

namespace Tests\Helpers;

use App\Users\User;
use App\Users\UserFactory;
use App\Users\UserManager;
use Mockery as m;
use Mockery\CompositeExpectation;
use Mockery\MockInterface;

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
