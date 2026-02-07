<?php

namespace App\Exceptions;

class BusinessException extends ApiException
{
    public function __construct(string $message, array $errors = [])
    {
        parent::__construct($message, 422, $errors);
    }
}
