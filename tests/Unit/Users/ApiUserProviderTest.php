<?php

namespace Tests\Unit\Users;

use App\Users\ApiUserProvider;
use App\Users\UserManager;
use Tests\Helpers\ApiHelper;
use Tests\Helpers\ReflectionHelper;
use Tests\Helpers\UsersHelper;
use Tests\TestCase;

final class ApiUserProviderTest extends TestCase
{
    use ApiHelper;
    use ReflectionHelper;
    use UsersHelper;

    private function getApiUserProvider(UserManager $userManager = null): ApiUserProvider
    {
        return new ApiUserProvider($userManager ?: $this->createUserManager());
    }

    private function setUpRetrieveByIdTest(
        bool $withUser = true,
        bool $withCorrectId = true,
        bool $withFetchedUser = false
    ): array {
        $uuid = $this->getFaker()->uuid;
        $user = $this->createUser($withCorrectId ? $uuid : $this->getFaker()->uuid);
        $userManager = $this->createUserManager();
        $this->mockUserManagerAuthenticatedUser($userManager, $withUser ? $user : null);
        $apiUserProvider = $this->getApiUserProvider($userManager);
        if ($withFetchedUser) {
            $this->setPrivateProperty($apiUserProvider, 'user', $user);
        }

        return [$apiUserProvider, $uuid, $user, $userManager];
    }

    public function testRetrieveById(): void
    {
        /** @var ApiUserProvider $apiUserProvider */
        [$apiUserProvider, $uuid, $user] = $this->setUpRetrieveByIdTest();

        $this->assertEquals($user, $apiUserProvider->retrieveById($uuid));
    }

    public function testRetrieveByIdWithoutUser(): void
    {
        /** @var ApiUserProvider $apiUserProvider */
        [$apiUserProvider, $uuid] = $this->setUpRetrieveByIdTest(withUser: false);

        $this->assertNull($apiUserProvider->retrieveById($uuid));
    }

    public function testRetrieveByIdWithoutCorrectId(): void
    {
        /** @var ApiUserProvider $apiUserProvider */
        [$apiUserProvider, $uuid] = $this->setUpRetrieveByIdTest(withCorrectId: false);

        $this->assertNull($apiUserProvider->retrieveById($uuid));
        $this->assertNull($this->getPrivateProperty($apiUserProvider, 'user'));
    }

    public function testRetrieveByIdWithAlreadyFetchedUser(): void
    {
        /** @var ApiUserProvider $apiUserProvider */
        [$apiUserProvider, $uuid, $user, $userManager] = $this->setUpRetrieveByIdTest(withFetchedUser: true);

        $this->assertEquals($user, $apiUserProvider->retrieveById($uuid));
        $userManager->shouldNotHaveReceived('authenticatedUser');
    }

    private function setUpRetrieveByCredentialsTest(bool $withValidCredentials = true): array
    {
        $email = $this->getFaker()->safeEmail;
        $password = $this->getFaker()->password(12);
        $user = $this->createUser();
        $userManager = $this->createUserManager();
        $this->mockUserManagerLogin($userManager, $withValidCredentials ? $user : null, $email, $password);
        $apiUserProvider = $this->getApiUserProvider($userManager);

        return [$apiUserProvider, $email, $password, $user];
    }

    public function testRetrieveByCredentials(): void
    {
        /** @var ApiUserProvider $apiUserProvider */
        [$apiUserProvider, $email, $password, $user] = $this->setUpRetrieveByCredentialsTest();

        $this->assertEquals($user, $apiUserProvider->retrieveByCredentials(['email' => $email, 'password' => $password]));
        $this->assertEquals($user, $this->getPrivateProperty($apiUserProvider, 'user'));
    }

    public function testRetrieveByCredentialsWithInvalidCredentials(): void
    {
        /** @var ApiUserProvider $apiUserProvider */
        [$apiUserProvider, $email, $password, $user] = $this->setUpRetrieveByCredentialsTest(withValidCredentials: false);

        $this->assertNull($apiUserProvider->retrieveByCredentials(['email' => $email, 'password' => $password]));
    }

    public function testRetrieveByCredentialsWithoutEmail(): void
    {
        /** @var ApiUserProvider $apiUserProvider */
        [$apiUserProvider, $email, $password] = $this->setUpRetrieveByCredentialsTest();

        $this->assertNull($apiUserProvider->retrieveByCredentials(['password' => $password]));
    }

    public function testRetrieveByCredentialsWithoutPassword(): void
    {
        /** @var ApiUserProvider $apiUserProvider */
        [$apiUserProvider, $email] = $this->setUpRetrieveByCredentialsTest();

        $this->assertNull($apiUserProvider->retrieveByCredentials(['email' => $email]));
    }

    private function setUpValidateCredentialsTest(bool $withCachedUser = true): array
    {
        $email = $this->getFaker()->safeEmail;
        $password = $this->getFaker()->password(12);
        $user = $this->createUser(null, $email);
        $apiUserProvider = $this->getApiUserProvider();
        if ($withCachedUser) {
            $this->setPrivateProperty($apiUserProvider, 'user', $user);
        }

        return [$apiUserProvider, $user, $email, $password];
    }

    public function testValidateCredentials(): void
    {
        /** @var ApiUserProvider $apiUserProvider */
        [$apiUserProvider, $user, $email, $password] = $this->setUpValidateCredentialsTest();

        $this->assertTrue($apiUserProvider->validateCredentials($user, ['email' => $email, 'password' => $password]));
    }

    public function testValidateCredentialsWithoutMatchingEmail(): void
    {
        /** @var ApiUserProvider $apiUserProvider */
        [$apiUserProvider, $user, $email, $password] = $this->setUpValidateCredentialsTest();

        $this->assertFalse($apiUserProvider->validateCredentials(
            $user,
            ['email' => \sprintf('%s%s', $this->getFaker()->word, $email), 'password' => $password]
        ));
    }

    public function testValidateCredentialsWithoutCachedUser(): void
    {
        /** @var ApiUserProvider $apiUserProvider */
        [$apiUserProvider, $user, $email, $password] = $this->setUpValidateCredentialsTest(withCachedUser: false);

        $this->assertFalse($apiUserProvider->validateCredentials($user, ['email' => $email, 'password' => $password]));
    }

    public function testValidateCredentialsWithoutMatchingUsers(): void
    {
        /** @var ApiUserProvider $apiUserProvider */
        [$apiUserProvider, $user, $email, $password] = $this->setUpValidateCredentialsTest();

        $this->assertFalse($apiUserProvider->validateCredentials($this->createUser(null, $email), ['email' => $email, 'password' => $password]));
    }
}