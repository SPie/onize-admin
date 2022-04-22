<?php

namespace App\Api\Exceptions;

final class ServerException extends ApiException
{
    public function __construct(string $message = "")
    {
        parent::__construct($message, 500);
    }
}