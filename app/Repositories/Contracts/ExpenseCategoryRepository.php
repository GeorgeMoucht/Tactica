<?php

namespace App\Repositories\Contracts;

use App\Models\ExpenseCategory;
use Illuminate\Support\Collection;

interface ExpenseCategoryRepository
{
    public function all(): Collection;

    public function findById(int $id): ?ExpenseCategory;

    public function create(array $data): ExpenseCategory;

    public function update(int $id, array $data): ?ExpenseCategory;

    public function delete(int $id): bool;

    public function hasExpenses(int $id): bool;
}
