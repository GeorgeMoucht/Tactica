<?php

namespace App\Data\DTO\Auth;

readonly class LoginDTO
{
    public function __construct(
        public string $email,
        public string $password
    ) { }
}