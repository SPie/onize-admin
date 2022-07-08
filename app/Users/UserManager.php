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
        return $this->createUserFromResponse($this->apiClient->register($email, $password));
    }

    public function login(string $email, string $password): ?User
    {
        try {
            return $this->createUserFromResponse($this->apiClient->authenticate($email, $password));
        } catch (AuthorizationException $e) {
            return null;
        }
    }

    public function authenticatedUser(): ?User
    {
        try {
            return $this->createUserFromResponse($this->apiClient->authenticatedUser());
        } catch (AuthenticationException $e) {
            return null;
        }
    }

    public function editProfile(string $email): User
    {
        return $this->createUserFromResponse($this->apiClient->updateProfile($email));
    }

    public function editPassword(string $currentPassword, string $newPassword): User
    {
        return $this->createUserFromResponse($this->apiClient->updatePassword($currentPassword, $newPassword));
    }

    private function createUserFromResponse(array $userResponse): User
    {
        return $this->userFactory->create($userResponse[User::PROPERTY_UUID], $userResponse[User::PROPERTY_EMAIL]);
    }
}
