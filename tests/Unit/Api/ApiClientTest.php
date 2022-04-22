<?php

namespace Tests\Unit\Api;

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
}