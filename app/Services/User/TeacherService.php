<?php

namespace App\Services\User;

use App\Repositories\Contracts\UserRepository;
use Illuminate\Support\Collection;

class TeacherService
{
    private readonly UserRepository $users;

    public function __construct(UserRepository $users)
    {
        $this->users = $users;
    }

    /** @return Collection <int, \App\Models\User> */
    public function list(): Collection
    {
        return $this->users->listTeachers();
    }
}