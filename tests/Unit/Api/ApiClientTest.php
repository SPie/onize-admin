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

    private function setUpRegisterTest(
        bool $withValidationException = false,
        bool $withClientError = false,
        bool $withServerError = false,
        bool $withRefreshToken = true,
        bool $withAuthToken = true
    ): array {
        $email = $this->getFaker()->safeEmail;
        $password = $this->getFaker()->password;
        $uuid = $this->getFaker()->uuid;
        $errorField = $this->getFaker()->word;
        $errorMessage = $this->getFaker()->word;
        $body = \json_encode(
            $withValidationException
                ? [
                    'errors' => [
                        $errorField => [$errorMessage],
                    ],
                ]
                : [
                    'user' => [
                        'uuid'  => $uuid,
                        'email' => $email,
                    ],
                ]
        );
        $status = 200;
        if ($withClientError) {
            $status = 400;
        }
        if ($withValidationException) {
            $status = 422;
        }
        if ($withServerError) {
            $status = 500;
        }
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
            ]
        );
        $tokenStore = $this->createTokenStore();
        $apiClient = $this->getApiClient($client, $tokenStore);

        return [$apiClient, $email, $password, $uuid, $errorField, $errorMessage, $tokenStore, $authToken, $refreshToken];
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

    public function testRegisterWithValidationErrors(): void
    {
        /** @var ApiClient $apiClient */
        [$apiClient, $email, $password, $uuid, $errorField, $errorMessage] = $this->setUpRegisterTest(withValidationException: true);

        try {
            $apiClient->register($email, $password);

            $this->fail('ValidationException expected.');
        } catch (ValidationException $e) {
            $this->assertEquals([$errorField => [$errorMessage]], $e->getErrors());
        } catch (\Exception $e) {
            $this->fail('ValidationException expected.');
        }
    }

    public function testRegisterWithRequestException(): void
    {
        /** @var ApiClient $apiClient */
        [$apiClient, $email, $password] = $this->setUpRegisterTest(withClientError: true);

        $this->expectException(ClientException::class);

        $apiClient->register($email, $password);
    }

    public function testRegisterWithServerException(): void
    {
        /** @var ApiClient $apiClient */
        [$apiClient, $email, $password] = $this->setUpRegisterTest(withServerError: true);

        $this->expectException(ServerException::class);

        $apiClient->register($email, $password);
    }

    public function testRegisterWithStoreTokens(): void
    {
        /** @var ApiClient $apiClient */
        [$apiClient,
            $email,
            $password,
            $uuid,
            $errorField,
            $errorMessage,
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
            $errorField,
            $errorMessage,
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
            $errorField,
            $errorMessage,
            $tokenStore,
        ] = $this->setUpRegisterTest(withRefreshToken: false, withAuthToken: false);

        $apiClient->register($email, $password);

        $tokenStore->shouldNotHaveReceived('storeTokens');
    }

    private function setUpAuthenticatedUserTest(
        bool $withAuthenticatedUser = true,
        bool $withClientException = false,
        bool $withServerException = false
    ): array {
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
        if (!$withAuthenticatedUser) {
            $status = 401;
        }
        if ($withClientException) {
            $status = 400;
        }
        if ($withServerException) {
            $status = 500;
        }
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

    public function testAuthenticatedUserWithoutAuthenticatedUser(): void
    {
        /** @var ApiClient $apiClient */
        [$apiClient] = $this->setUpAuthenticatedUserTest(withAuthenticatedUser: false);

        $this->expectException(AuthenticationException::class);

        $apiClient->authenticatedUser();
    }

    public function testAuthenticatedUserWithClientException(): void
    {
        /** @var ApiClient $apiClient */
        [$apiClient] = $this->setUpAuthenticatedUserTest(withClientException: true);

        $this->expectException(ClientException::class);

        $apiClient->authenticatedUser();
    }

    public function testAuthenticatedUserWithServerException(): void
    {
        /** @var ApiClient $apiClient */
        [$apiClient] = $this->setUpAuthenticatedUserTest(withServerException: true);

        $this->expectException(ServerException::class);

        $apiClient->authenticatedUser();
    }

    private function setUpAuthenticateTest(bool $withValidInput = true, bool $withValidCredentials = true): array
    {
        $email = $this->getFaker()->safeEmail;
        $password = $this->getFaker()->password;
        $user = [$this->getFaker()->word => $this->getFaker()->word];
        $errorField = $this->getFaker()->word;
        $errorMessage = $this->getFaker()->word;
        $statusCode = 200;
        if (!$withValidInput) {
            $statusCode = 422;
        }
        if (!$withValidCredentials) {
            $statusCode = 403;
        }
        $response = $this->createResponse();
        $this->mockResponseGetStatusCode($response, $statusCode);
        $this->mockResponseGetBody(
            $response,
            \json_encode(
                $withValidInput
                    ? ['user' => $user]
                    : [
                    'errors' => [
                        $errorField => [$errorMessage],
                    ],
                ]
            )
        );
        $client = $this->createHttpClient();
        $this->mockHttpClientPost($client, $response, 'auth', ['email' => $email, 'password' => $password]);
        $apiClient = $this->getApiClient($client);

        return [$apiClient, $email, $password, $user, $errorField, $errorMessage];
    }

    public function testAuthenticateWithSuccess(): void
    {
        /** @var ApiClient $apiClient */
        [$apiClient, $email, $password, $user] = $this->setUpAuthenticateTest();

        $this->assertEquals($user, $apiClient->authenticate($email, $password));
    }

    public function testAuthenticateWithValidationError(): void
    {
        /** @var ApiClient $apiClient */
        [$apiClient, $email, $password, $user, $errorField, $errorMessage] = $this->setUpAuthenticateTest(withValidInput: false);

        try {
            $apiClient->authenticate($email, $password);

            $this->fail('ValidationException expected.');
        } catch (ValidationException $e) {
            $this->assertEquals([$errorField => [$errorMessage]], $e->getErrors());
        } catch (\Exception $e) {
            $this->fail('ValidationException expected.');
        }
    }

    public function testAuthenticateWithInvalidCredentials(): void
    {
        /** @var ApiClient $apiClient */
        [$apiClient, $email, $password] = $this->setUpAuthenticateTest(withValidCredentials: false);

        $this->expectException(AuthorizationException::class);

        $apiClient->authenticate($email, $password);
    }
}