<?php

namespace App\Repositories\Eloquent;

use App\Models\Expense;
use App\Repositories\Contracts\ExpenseRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class EloquentExpenseRepository implements ExpenseRepository
{
    public function paginate(array $filters = []): LengthAwarePaginator
    {
        $q          = $filters['query'] ?? null;
        $perPage    = (int) ($filters['perPage'] ?? 10);
        $categoryId = $filters['category_id'] ?? null;
        $status     = $filters['status'] ?? null;
        $dateFrom   = $filters['date_from'] ?? null;
        $dateTo     = $filters['date_to'] ?? null;
        $sortBy     = $filters['sort_by'] ?? 'date';
        $sortOrder  = $filters['sort_order'] ?? 'desc';

        $allowedSortFields = ['id', 'description', 'amount', 'date', 'status', 'created_at'];

        if (!in_array($sortBy, $allowedSortFields)) {
            $sortBy = 'date';
        }

        $sortOrder = strtolower($sortOrder) === 'asc' ? 'asc' : 'desc';

        return Expense::query()
            ->with('category')
            ->when($q, function ($query) use ($q) {
                $query->where('description', 'like', "%$q%");
            })
            ->when($categoryId !== null, fn($query) => $query->where('expense_category_id', $categoryId))
            ->when($status !== null, fn($query) => $query->where('status', $status))
            ->when($dateFrom !== null, fn($query) => $query->where('date', '>=', $dateFrom))
            ->when($dateTo !== null, fn($query) => $query->where('date', '<=', $dateTo))
            ->orderBy($sortBy, $sortOrder)
            ->paginate($perPage);
    }

    public function findById(int $id): ?Expense
    {
        return Expense::with('category')->find($id);
    }

    public function create(array $data): Expense
    {
        $expense = Expense::create($data);
        return $this->findById($expense->id);
    }

    public function update(int $id, array $data): ?Expense
    {
        $expense = Expense::find($id);
        if (!$expense) return null;

        $expense->update($data);
        return $this->findById($expense->id);
    }

    public function delete(int $id): bool
    {
        $expense = Expense::find($id);
        if (!$expense) return false;

        $expense->delete();
        return true;
    }
}
