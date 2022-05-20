<?php

namespace App\Api\Exceptions;

final class AuthorizationException extends ApiException
{
    public function __construct(string $message = "")
    {
        parent::__construct($message, 403);
    }
}