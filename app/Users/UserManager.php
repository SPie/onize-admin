<?php

namespace App\Users;

use App\Api\ApiClient;

class UserManager
{
    public function __construct(private ApiClient $onizeApiClient, private UserFactory $userFactory)
    {
    }

    public function register(string $email, string $password): User
    {
        $response = $this->onizeApiClient->register($email, $password);

        return $this->userFactory->create(
            $response[User::PROPERTY_UUID],
            $response[User::PROPERTY_EMAIL]
        );
    }
}