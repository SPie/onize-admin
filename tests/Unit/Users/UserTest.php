<?php

namespace Tests\Unit\Users;

use App\Users\User;
use Tests\TestCase;

final class UserTest extends TestCase
{
    private function getUser(string $uuid = null, string $email = null): User
    {
        return new User(
            $uuid ?: $this->getFaker()->uuid,
            $email ?: $this->getFaker()->safeEmail
        );
    }

    public function testAuthIdentifierName(): void
    {
        $this->assertEquals('uuid', $this->getUser()->getAuthIdentifierName());
    }

    public function testGetAuthIdentifier(): void
    {
        $uuid = $this->getFaker()->uuid;

        $this->assertEquals($uuid, $this->getUser($uuid)->getAuthIdentifier());
    }

    public function testGetAuthPassword(): void
    {
        $this->assertEquals('', $this->getUser()->getAuthPassword());
    }

    public function testGetRememberToken(): void
    {
        $this->assertEquals('', $this->getUser()->getRememberToken());
    }

    public function testGetRememberTokenName(): void
    {
        $this->assertEquals('', $this->getUser()->getRememberTokenName());
    }
}