<?php

namespace App\Services\Expense;

use App\Exceptions\BusinessException;
use App\Exceptions\NotFoundException;
use App\Models\ExpenseCategory;
use App\Repositories\Contracts\ExpenseCategoryRepository;
use Illuminate\Support\Collection;

class ExpenseCategoryService
{
    private readonly ExpenseCategoryRepository $categories;

    public function __construct(ExpenseCategoryRepository $categories)
    {
        $this->categories = $categories;
    }

    public function all(): Collection
    {
        return $this->categories->all();
    }

    public function create(array $data): ExpenseCategory
    {
        return $this->categories->create($data);
    }

    public function update(int $id, array $data): ExpenseCategory
    {
        $category = $this->categories->update($id, $data);
        if (!$category) {
            throw new NotFoundException('Expense category not found.');
        }
        return $category;
    }

    public function delete(int $id): bool
    {
        $category = $this->categories->findById($id);
        if (!$category) {
            throw new NotFoundException('Expense category not found.');
        }

        if ($this->categories->hasExpenses($id)) {
            throw new BusinessException('Cannot delete category that has expenses.');
        }

        return $this->categories->delete($id);
    }
}
