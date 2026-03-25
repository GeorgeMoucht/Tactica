<?php

namespace App\Repositories\Contracts;

use App\Models\Expense;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface ExpenseRepository
{
    public function paginate(array $filters = []): LengthAwarePaginator;

    public function findById(int $id): ?Expense;

    public function create(array $data): Expense;

    public function update(int $id, array $data): ?Expense;

    public function delete(int $id): bool;
}
