<?php

namespace Tests\Unit\Users;

use App\Api\Exceptions\AuthenticationException;
use App\Api\Exceptions\ValidationException;
use App\Api\ApiClient;
use App\Users\UserFactory;
use App\Users\UserManager;
use Tests\Helpers\ApiHelper;
use Tests\Helpers\UsersHelper;
use Tests\TestCase;

class UserManagerTest extends TestCase
{
    use ApiHelper;
    use UsersHelper;

    private function getUserManager(ApiClient $onizeApiClient = null, UserFactory $userFactory = null): UserManager
    {
        return new UserManager(
            $onizeApiClient ?: $this->createApiClient(),
            $userFactory ?: $this->createUserFactory()
        );
    }

    private function setUpRegisterTest(bool $withException = false): array
    {
        $email = $this->getFaker()->safeEmail;
        $password = $this->getFaker()->password;
        $uuid = $this->getFaker()->uuid;
        $apiClient = $this->createApiClient();
        $this->mockApiClientRegister(
            $apiClient,
            $withException ? $this->createValidationException() : ['uuid' => $uuid, 'email' => $email],
            $email,
            $password
        );
        $user = $this->createUser();
        $userFactory = $this->createUserFactory();
        $this->mockUserFactoryCreate($userFactory, $user, $uuid, $email);
        $userManager = $this->getUserManager($apiClient, $userFactory);

        return [$userManager, $email, $password, $user];
    }

    public function testRegister(): void
    {
        /** @var UserManager $userManager */
        [$userManager, $email, $password, $user] = $this->setUpRegisterTest();

        $this->assertEquals($user, $userManager->register($email, $password));
    }

    public function testRegisterWithValidationErrors(): void
    {
        /** @var UserManager $userManager */
        [$userManager, $email, $password] = $this->setUpRegisterTest(true);

        $this->expectException(ValidationException::class);

        $userManager->register($email, $password);
    }

    private function setUpAuthenticatedUserTest(bool $withAuthenticatedUser = true): array
    {
        $uuid = $this->getFaker()->uuid;
        $email = $this->getFaker()->safeEmail;
        $user = $this->createUser();
        $userFactory = $this->createUserFactory();
        $this->mockUserFactoryCreate($userFactory, $user, $uuid, $email);
        $apiClient = $this->createApiClient();
        $this->mockApiClientAuthenticatedUser(
            $apiClient,
            $withAuthenticatedUser ? ['uuid' => $uuid, 'email' => $email] : new AuthenticationException()
        );
        $userManager = $this->getUserManager($apiClient, $userFactory);

        return [$userManager, $user];
    }

    public function testAuthenticatedUser(): void
    {
        /** @var UserManager $userManager */
        [$userManager, $user] = $this->setUpAuthenticatedUserTest();

        $this->assertEquals($user, $userManager->authenticatedUser());
    }

    public function testAuthenticatedUserWithoutAuthenticatedUser(): void
    {
        /** @var UserManager $userManager */
        [$userManager] = $this->setUpAuthenticatedUserTest(withAuthenticatedUser: false);

        $this->assertNull($userManager->authenticatedUser());
    }
}