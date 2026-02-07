<?php

namespace App\Exceptions;

class InvalidTokenException extends ApiException
{
    public function __construct(string $message = 'Invalid or expired token', array $errors = [])
    {
        parent::__construct($message, 401, $errors);
    }
}
