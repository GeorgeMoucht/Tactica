<?php

namespace App\Exceptions;

class ConflictException extends ApiException
{
    public function __construct(string $message = 'Resource conflict', array $errors = [])
    {
        parent::__construct($message, 409, $errors);
    }
}
