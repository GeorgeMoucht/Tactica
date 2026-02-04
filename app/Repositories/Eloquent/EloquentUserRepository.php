<?php

namespace App\Repositories\Eloquent;

use App\Models\User;
use App\Repositories\Contracts\UserRepository;
use Illuminate\Support\Collection;

class EloquentUserRepository implements UserRepository
{
    public function findByEmail(string $email): ?User
    {
        return User::where('email', $email)->first();
    }

    public function findById(int $id): ?User
    {
        return User::find($id);
    }

    public function listTeachers(): Collection
    {
        return User::query()
            ->select(['id', 'name', 'email', 'role'])
            ->whereIn('role', ['teacher', 'admin'])
            ->orderBy('name')
            ->get();
    }

    public function create(array $data): User
    {
        return User::create($data);
    }

    public function save(User $user): bool
    {
        return $user->save();
    }
}