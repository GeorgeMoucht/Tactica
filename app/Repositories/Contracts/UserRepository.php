<?php

namespace App\Repositories\Contracts;

use App\Models\User;

interface UserRepository
{
    public function findByEmail(string $email): ?User;

    /** @param array{name:string,email:string,password:string} $data */
    public function create(array $data): User;

    public function save(User $user): bool;
}