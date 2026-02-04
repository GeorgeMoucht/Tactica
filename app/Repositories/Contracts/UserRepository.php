<?php

namespace App\Repositories\Contracts;

use App\Models\User;
use Illuminate\Support\Collection;

interface UserRepository
{
    public function findByEmail(string $email): ?User;

    public function findById(int $id): ?User;

    /** @param array{name:string,email:string,password:string} $data */
    public function create(array $data): User;

    public function save(User $user): bool;

    /** @return Collection<int, User> */
    public function listTeachers(): Collection;
}