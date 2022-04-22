<?php

namespace App\Api\Exceptions;

final class ClientException extends ApiException
{
    public function __construct(string $message = "")
    {
        parent::__construct($message, 400);
    }
}