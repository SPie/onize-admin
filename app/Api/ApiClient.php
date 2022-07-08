<?php

namespace App\Api;

use App\Auth\TokenStore;
use App\Users\User;
use Psr\Http\Message\ResponseInterface;

class ApiClient
{
    private const ENDPOINT_REGISTER           = 'users';
    private const ENDPOINT_AUTHENTICATED_USER = 'me';
    private const ENDPOINT_AUTHENTICATE       = 'auth';
    private const ENDPOINT_USERS              = 'users';
    private const ENDPOINT_USERS_PASSWORD     = 'users/password';

    private const HEADER_AUTH_TOKEN    = 'x-authorize';
    private const HEADER_REFRESH_TOKEN = 'x-refresh';

    private const RESPONSE_USER = 'user';

    public function __construct(private readonly HttpClient $client, private readonly TokenStore $tokenStore)
    {
    }

    private function doGet(string $path): array
    {
        return $this->handleResponse($this->client->get($path, [], $this->createHeaders()));
    }

    private function doPost(string $path, array $data): array
    {
        return $this->handleResponse($this->client->post($path, $data, $this->createHeaders()));
    }

    private function doPatch(string $path, array $data): array
    {
        return $this->handleResponse($this->client->patch($path, $data, $this->createHeaders()));
    }

    private function createHeaders(): array
    {
        $headers = [
            'Content-Type'             => 'application/json',
            'Accept'                   => 'application/json',
        ];

        if (!empty($this->tokenStore->getAuthToken())) {
            $headers[self::HEADER_AUTH_TOKEN] = \sprintf('Bearer %s', $this->tokenStore->getAuthToken());
        }
        if (!empty($this->tokenStore->getRefreshToken())) {
            $headers[self::HEADER_REFRESH_TOKEN] = \sprintf('Bearer %s', $this->tokenStore->getRefreshToken());
        }

        return $headers;
    }

    private function handleResponse(ResponseInterface $response): array
    {
        $this->storeAuthTokens($response);

        return \json_decode($response->getBody(), true);
    }

    private function storeAuthTokens(ResponseInterface $response): self
    {
        $authTokenHeaders = $response->getHeader(self::HEADER_AUTH_TOKEN);
        $refreshTokenHeaders = $response->getHeader(self::HEADER_REFRESH_TOKEN);

        $authToken = !empty($authTokenHeaders) ? \explode(' ', \reset($authTokenHeaders))[1] : null;
        $refreshToken = !empty($refreshTokenHeaders) ? \explode(' ', \reset($refreshTokenHeaders))[1] : null;

        if (!empty($authToken)) {
            $this->tokenStore->storeTokens($authToken, $refreshToken);
        }

        return $this;
    }

    public function register(string $email, string $password): array
    {
        $response = $this->doPost(
            self::ENDPOINT_REGISTER,
            [User::PROPERTY_EMAIL => $email, User::PROPERTY_PASSWORD => $password]
        );

        return $response[self::RESPONSE_USER];
    }

    public function authenticate(string $email, string $password): array
    {
        $response = $this->doPost(
            self::ENDPOINT_AUTHENTICATE,
            [User::PROPERTY_EMAIL => $email, User::PROPERTY_PASSWORD => $password]
        );

        return $response[self::RESPONSE_USER];
    }

    public function authenticatedUser(): array
    {
        $response = $this->doGet(self::ENDPOINT_AUTHENTICATED_USER);

        return $response[self::RESPONSE_USER];
    }

    public function updateProfile(string $email): array
    {
        $response = $this->doPatch(self::ENDPOINT_USERS, ['email' => $email]);

        return $response[self::RESPONSE_USER];
    }

    public function updatePassword(string $currentPassword, string $newPassword): array
    {
        $response = $this->doPatch(
            self::ENDPOINT_USERS_PASSWORD,
            [
                'password'        => $newPassword,
                'currentPassword' => $currentPassword,
            ]
        );

        return $response[self::RESPONSE_USER];
    }
}
