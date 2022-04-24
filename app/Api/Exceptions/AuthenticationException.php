<?php

namespace App\Api\Exceptions;

final class AuthenticationException extends ApiException
{
    public function __construct(string $message = "")
    {
        parent::__construct($message, 401);
    }
}