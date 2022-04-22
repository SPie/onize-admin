<?php

namespace Tests\Unit\Users;

use App\Users\User;
use App\Users\UserFactory;
use Tests\TestCase;

class UserFactoryTest extends TestCase
{
    private function getUserFactory(): UserFactory
    {
        return new UserFactory();
    }

    public function testCreate(): void
    {
        $uuid = $this->getFaker()->uuid;
        $email = $this->getFaker()->email;

        $this->assertEquals(
            new User($uuid, $email),
            $this->getUserFactory()->create($uuid, $email)
        );
    }
}