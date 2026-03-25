<?php

namespace App\Repositories\Eloquent;

use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Repositories\Contracts\ExpenseCategoryRepository;
use Illuminate\Support\Collection;

class EloquentExpenseCategoryRepository implements ExpenseCategoryRepository
{
    public function all(): Collection
    {
        return ExpenseCategory::orderBy('name')->get();
    }

    public function findById(int $id): ?ExpenseCategory
    {
        return ExpenseCategory::find($id);
    }

    public function create(array $data): ExpenseCategory
    {
        return ExpenseCategory::create($data);
    }

    public function update(int $id, array $data): ?ExpenseCategory
    {
        $category = ExpenseCategory::find($id);
        if (!$category) return null;

        $category->update($data);
        return $category;
    }

    public function delete(int $id): bool
    {
        $category = ExpenseCategory::find($id);
        if (!$category) return false;

        $category->delete();
        return true;
    }

    public function hasExpenses(int $id): bool
    {
        return Expense::where('expense_category_id', $id)->exists();
    }
}
