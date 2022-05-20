<?php

namespace App\Users;

use App\Api\ApiClient;
use App\Api\Exceptions\AuthenticationException;
use App\Api\Exceptions\AuthorizationException;

class UserManager
{
    public function __construct(private readonly ApiClient $apiClient, private readonly UserFactory $userFactory)
    {
    }

    public function register(string $email, string $password): User
    {
        $response = $this->apiClient->register($email, $password);

        return $this->userFactory->create(
            $response[User::PROPERTY_UUID],
            $response[User::PROPERTY_EMAIL]
        );
    }

    public function login(string $email, string $password): ?User
    {
        try {
            $response = $this->apiClient->authenticate($email, $password);
        } catch (AuthorizationException $e) {
            return null;
        }

        return $this->userFactory->create(
            $response[User::PROPERTY_UUID],
            $response[User::PROPERTY_EMAIL]
        );
    }

    public function authenticatedUser(): ?User
    {
        try {
            $response = $this->apiClient->authenticatedUser();
        } catch (AuthenticationException $e) {
            return null;
        }

        return $this->userFactory->create(
            $response[User::PROPERTY_UUID],
            $response[User::PROPERTY_EMAIL],
        );
    }
}
