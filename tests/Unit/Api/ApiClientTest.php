<?php

namespace Tests\Unit\Api;

use App\Api\Exceptions\AuthenticationException;
use App\Api\Exceptions\AuthorizationException;
use App\Api\Exceptions\ClientException;
use App\Api\Exceptions\ServerException;
use App\Api\Exceptions\ValidationException;
use App\Api\HttpClient;
use App\Api\ApiClient;
use App\Auth\TokenStore;
use Tests\Helpers\ApiHelper;
use Tests\Helpers\AuthHelper;
use Tests\TestCase;

class ApiClientTest extends TestCase
{
    use ApiHelper;
    use AuthHelper;

    private function getApiClient(HttpClient $client = null, TokenStore $tokenStore = null): ApiClient
    {
        return new ApiClient(
            $client ?: $this->createHttpClient(),
            $tokenStore ?: $this->createTokenStore()
        );
    }

    private function setUpRegisterTest(bool $withRefreshToken = true, bool $withAuthToken = true): array
    {
        $email = $this->getFaker()->safeEmail;
        $password = $this->getFaker()->password;
        $uuid = $this->getFaker()->uuid;
        $body = \json_encode([
            'user' => [
                'uuid'  => $uuid,
                'email' => $email,
            ],
        ]);
        $status = 200;
        $authToken = $this->getFaker()->word;
        $refreshToken = $this->getFaker()->word;
        $response = $this->createResponse();
        $this->mockResponseGetBody($response, $body);
        $this->mockResponseGetStatusCode($response, $status);
        $this->mockResponseGetHeader($response, $withAuthToken ? [\sprintf('Bearer %s', $authToken)] : [], 'x-authorize');
        $this->mockResponseGetHeader($response, $withRefreshToken ? [\sprintf('Bearer %s', $refreshToken)] : [], 'x-refresh');
        $client = $this->createHttpClient();
        $this->mockHttpClientPost(
            $client,
            $response,
            'users',
            [
                'email'    => $email,
                'password' => $password,
            ],
            [
                'Content-Type' => 'application/json',
                'Accept'       => 'application/json',
            ]
        );
        $tokenStore = $this->createTokenStore();
        $apiClient = $this->getApiClient($client, $tokenStore);

        return [$apiClient, $email, $password, $uuid, $tokenStore, $authToken, $refreshToken];
    }

    public function testRegister(): void
    {
        /** @var ApiClient $apiClient */
        [$apiClient, $email, $password, $uuid] = $this->setUpRegisterTest();

        $this->assertEquals(
            [
                'uuid'  => $uuid,
                'email' => $email,
            ],
            $apiClient->register($email, $password)
        );
    }

    public function testRegisterWithStoreTokens(): void
    {
        /** @var ApiClient $apiClient */
        [$apiClient,
            $email,
            $password,
            $uuid,
            $tokenStore,
            $authToken,
            $refreshToken
        ] = $this->setUpRegisterTest();

        $apiClient->register($email, $password);

        $this->assertTokenStoreStoreTokens($tokenStore, $authToken, $refreshToken);
    }

    public function testRegisterWithoutRefreshToken(): void
    {
        /** @var ApiClient $apiClient */
        [$apiClient,
            $email,
            $password,
            $uuid,
            $tokenStore,
            $authToken,
        ] = $this->setUpRegisterTest(withRefreshToken: false);

        $apiClient->register($email, $password);

        $this->assertTokenStoreStoreTokens($tokenStore, $authToken, null);
    }

    public function testRegisterWithoutAuthTokens(): void
    {
        /** @var ApiClient $apiClient */
        [$apiClient,
            $email,
            $password,
            $uuid,
            $tokenStore,
        ] = $this->setUpRegisterTest(withRefreshToken: false, withAuthToken: false);

        $apiClient->register($email, $password);

        $tokenStore->shouldNotHaveReceived('storeTokens');
    }

    private function setUpAuthenticatedUserTest(): array
    {
        $user = [
            'uuid'  => $this->getFaker()->uuid,
            'email' => $this->getFaker()->safeEmail,
        ];
        $authToken = $this->getFaker()->word;
        $refreshToken = $this->getFaker()->word;
        $tokenStore = $this->createTokenStore();
        $this->mockTokenStoreGetAuthToken($tokenStore, $authToken);
        $this->mockTokenStoreGetRefreshToken($tokenStore, $refreshToken);
        $body = ['user' => $user];
        $status = 200;
        $response = $this->createResponse();
        $this->mockResponseGetStatusCode($response, $status);
        $this->mockResponseGetBody($response, \json_encode($body));
        $client = $this->createHttpClient();
        $this->mockHttpClientGet(
            $client,
            $response,
            'me',
            [],
            [
                'Content-Type' => 'application/json',
                'Accept'       => 'application/json',
                'x-authorize'  => \sprintf('Bearer %s', $authToken),
                'x-refresh'    => $refreshToken,
            ]
        );
        $apiClient = $this->getApiClient($client, $tokenStore);

        return [$apiClient, $user];
    }

    public function testAuthenticatedUser(): void
    {
        /** @var ApiClient $apiClient */
        [$apiClient, $user] = $this->setUpAuthenticatedUserTest();

        $this->assertEquals($user, $apiClient->authenticatedUser());
    }

    private function setUpAuthenticateTest(): array
    {
        $email = $this->getFaker()->safeEmail;
        $password = $this->getFaker()->password;
        $user = [$this->getFaker()->word => $this->getFaker()->word];
        $statusCode = 200;
        $response = $this->createResponse();
        $this->mockResponseGetStatusCode($response, $statusCode);
        $this->mockResponseGetBody($response, \json_encode(['user' => $user]));
        $client = $this->createHttpClient();
        $this->mockHttpClientPost(
            $client,
            $response,
            'auth',
            ['email' => $email, 'password' => $password],
            [
                'Content-Type' => 'application/json',
                'Accept'       => 'application/json',
            ]
        );
        $apiClient = $this->getApiClient($client);

        return [$apiClient, $email, $password, $user];
    }

    public function testAuthenticateWithSuccess(): void
    {
        /** @var ApiClient $apiClient */
        [$apiClient, $email, $password, $user] = $this->setUpAuthenticateTest();

        $this->assertEquals($user, $apiClient->authenticate($email, $password));
    }
}