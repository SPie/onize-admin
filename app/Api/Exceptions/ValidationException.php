<?php

namespace App\Api\Exceptions;

class ValidationException extends ApiException
{
    public function __construct(private array $errors = [])
    {
        parent::__construct('Validation error', 422);
    }

    public function getErrors(): array
    {
        return $this->errors;
    }
}