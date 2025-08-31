<?php

namespace App\Data\DTO\Auth;

readonly class RegisterDTO
{
    public function __construct(
        public string $name,
        public string $email,
        public string $password
    )
    { }
}