<?php

namespace Tests\Unit\Users;

use App\Api\Exceptions\AuthenticationException;
use App\Api\Exceptions\AuthorizationException;
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

    private function setUpLoginTest(bool $validCredentials = true): array
    {
        $email = $this->getFaker()->safeEmail;
        $password = $this->getFaker()->password;
        $uuid = $this->getFaker()->uuid;
        $user = $this->createUser();
        $userFactory = $this->createUserFactory();
        $this->mockUserFactoryCreate($userFactory, $user, $uuid, $email);
        $apiClient = $this->createApiClient();
        $this->mockApiClientAuthenticate(
            $apiClient,
            $validCredentials ? ['uuid' => $uuid, 'email' => $email] : new AuthorizationException(),
            $email,
            $password
        );
        $userManager = $this->getUserManager($apiClient, $userFactory);

        return [$userManager, $email, $password, $user];
    }

    public function testLogin(): void
    {
        /** @var UserManager $userManager */
        [$userManager, $email, $password, $user] = $this->setUpLoginTest();

        $this->assertEquals($user, $userManager->login($email, $password));
    }

    public function testLoginWithInvalidCredentials(): void
    {
        /** @var UserManager $userManager */
        [$userManager, $email, $password] = $this->setUpLoginTest(validCredentials: false);

        $this->assertNull($userManager->login($email, $password));
    }

    private function setUpEditProfileTest(): array
    {
        $email = $this->getFaker()->safeEmail;
        $uuid = $this->getFaker()->uuid;
        $user = $this->createUser();
        $userFactory = $this->createUserFactory();
        $this->mockUserFactoryCreate($userFactory, $user, $uuid, $email);
        $apiClient = $this->createApiClient();
        $this->mockApiClientUpdateProfile($apiClient, ['uuid' => $uuid, 'email' => $email], $email);
        $userManager = $this->getUserManager($apiClient, $userFactory);

        return [$userManager, $email, $user];
    }

    public function testEditProfile(): void
    {
        /** @var UserManager $userManager */
        [$userManager, $email, $user] = $this->setUpEditProfileTest();

        $this->assertEquals($user, $userManager->editProfile($email));
    }

    private function setUpEditPasswordTest(): array
    {
        $currentPassword = $this->getFaker()->password;
        $newPassword = $this->getFaker()->password;
        $uuid = $this->getFaker()->uuid;
        $email = $this->getFaker()->safeEmail;
        $user = $this->createUser();
        $userFactory = $this->createUserFactory();
        $this->mockUserFactoryCreate($userFactory, $user, $uuid, $email);
        $apiClient = $this->createApiClient();
        $this->mockApiClientUpdatePassword($apiClient, ['uuid' => $uuid, 'email' => $email], $currentPassword, $newPassword);
        $userManager = $this->getUserManager($apiClient, $userFactory);

        return [$userManager, $currentPassword, $newPassword, $user];
    }

    public function testEditPassword(): void
    {
        /** @var UserManager $userManager */
        [$userManager, $currentPassword, $newPassword, $user] = $this->setUpEditPasswordTest();

        $this->assertEquals($user, $userManager->editPassword($currentPassword, $newPassword));
    }
}