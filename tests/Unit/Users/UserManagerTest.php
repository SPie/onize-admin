<?php

namespace Tests\Unit\Users;

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
            $onizeApiClient ?: $this->createOnizeApiClient(),
            $userFactory ?: $this->createUserFactory()
        );
    }

    private function setUpRegisterTest(bool $withException = false): array
    {
        $email = $this->getFaker()->safeEmail;
        $password = $this->getFaker()->password;
        $uuid = $this->getFaker()->uuid;
        $apiClient = $this->createOnizeApiClient();
        $this->mockOnizeApiClientRegister(
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
}