<?php

namespace App\Services\Expense;

use App\Exceptions\BusinessException;
use App\Exceptions\NotFoundException;
use App\Models\Expense;
use App\Repositories\Contracts\ExpenseCategoryRepository;
use App\Repositories\Contracts\ExpenseRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ExpenseService
{
    private readonly ExpenseRepository $expenses;
    private readonly ExpenseCategoryRepository $categories;

    public function __construct(ExpenseRepository $expenses, ExpenseCategoryRepository $categories)
    {
        $this->expenses = $expenses;
        $this->categories = $categories;
    }

    public function list(array $filters): LengthAwarePaginator
    {
        return $this->expenses->paginate($filters);
    }

    public function detail(int $id): Expense
    {
        $expense = $this->expenses->findById($id);
        if (!$expense) {
            throw new NotFoundException('Expense not found.');
        }
        return $expense;
    }

    public function create(array $data): Expense
    {
        return $this->expenses->create($data);
    }

    public function update(int $id, array $data): Expense
    {
        $expense = $this->expenses->update($id, $data);
        if (!$expense) {
            throw new NotFoundException('Expense not found.');
        }
        return $expense;
    }

    public function delete(int $id): bool
    {
        $expense = $this->expenses->findById($id);
        if (!$expense) {
            throw new NotFoundException('Expense not found.');
        }
        return $this->expenses->delete($id);
    }

    public function markAsPaid(int $id): Expense
    {
        $expense = $this->expenses->findById($id);
        if (!$expense) {
            throw new NotFoundException('Expense not found.');
        }

        if ($expense->status === 'paid') {
            throw new BusinessException('Expense is already paid.');
        }

        $updated = $this->expenses->update($id, [
            'status'  => 'paid',
            'paid_at' => now(),
        ]);

        return $updated;
    }
}
