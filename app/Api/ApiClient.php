<?php

namespace App\Api;

use App\Api\Exceptions\ClientException;
use App\Api\Exceptions\ServerException;
use App\Api\Exceptions\ValidationException;
use App\Auth\TokenStore;
use Psr\Http\Message\ResponseInterface;

class ApiClient
{
    public function __construct(private readonly HttpClient $client, private readonly TokenStore $tokenStore)
    {
    }

    private function doPost(string $path, array $data): array
    {
        $response = $this->client->post($path, $data);
        if ($response->getStatusCode() === 400) {
            throw new ClientException();
        }
        if ($response->getStatusCode() === 422) {
            $responseBody = \json_decode($response->getBody(), true);
            throw new ValidationException($responseBody['errors']);
        }
        if ($response->getStatusCode() === 500) {
            throw new ServerException();
        }

        $this->storeAuthTokens($response);

        return \json_decode($response->getBody(), true);
    }

    private function storeAuthTokens(ResponseInterface $response): self
    {
        $authTokenHeaders = $response->getHeader('x-authorize');
        $refreshTokenHeaders = $response->getHeader('x-refresh');

        $authToken = !empty($authTokenHeaders) ? \explode(' ', \reset($authTokenHeaders))[1] : null;
        $refreshToken = !empty($refreshTokenHeaders) ? \explode(' ', \reset($refreshTokenHeaders))[1] : null;

        if (!empty($authToken)) {
            $this->tokenStore->storeTokens($authToken, $refreshToken);
        }

        return $this;
    }

    public function register(string $email, string $password): array
    {
        $response = $this->doPost('users', ['email' => $email, 'password' => $password]);

        return $response['user'];
    }
}
