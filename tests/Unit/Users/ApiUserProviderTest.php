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
    }

    public function testRetrieveByIdWithAlreadyFetchedUser(): void
    {
        /** @var ApiUserProvider $apiUserProvider */
        [$apiUserProvider, $uuid, $user, $userManager] = $this->setUpRetrieveByIdTest(withFetchedUser: true);

        $this->assertEquals($user, $apiUserProvider->retrieveById($uuid));
        $userManager->shouldNotHaveReceived('authenticatedUser');
    }
}