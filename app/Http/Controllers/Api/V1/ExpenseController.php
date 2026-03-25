<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\BaseApiController;
use App\Http\Requests\Expense\IndexExpenseRequest;
use App\Http\Requests\Expense\StoreExpenseRequest;
use App\Http\Requests\Expense\UpdateExpenseRequest;
use App\Http\Resources\Expense\ExpenseResource;
use App\Services\Expense\ExpenseService;

class ExpenseController extends BaseApiController
{
    private readonly ExpenseService $service;

    public function __construct(ExpenseService $service)
    {
        $this->service = $service;
    }

    // GET /api/v1/expenses
    public function index(IndexExpenseRequest $request)
    {
        $filters = [
            'query'       => $request->validated('query') ?? null,
            'perPage'     => (int) ($request->validated('pageSize') ?? 10),
            'category_id' => $request->validated('category_id') ?? null,
            'status'      => $request->validated('status') ?? null,
            'date_from'   => $request->validated('date_from') ?? null,
            'date_to'     => $request->validated('date_to') ?? null,
            'sort_by'     => $request->validated('sort_by') ?? 'date',
            'sort_order'  => $request->validated('sort_order') ?? 'desc',
        ];

        $paginator = $this->service->list($filters);

        return $this->paginatedSuccess($paginator, 'Expenses retrieved');
    }

    // POST /api/v1/expenses
    public function store(StoreExpenseRequest $request)
    {
        $expense = $this->service->create($request->validated());

        return $this->actionSuccess(
            'Expense created',
            new ExpenseResource($expense)
        );
    }

    // GET /api/v1/expenses/{id}
    public function show(int $id)
    {
        $expense = $this->service->detail($id);

        return $this->getSuccess(
            new ExpenseResource($expense),
            'Expense retrieved'
        );
    }

    // PUT /api/v1/expenses/{id}
    public function update(UpdateExpenseRequest $request, int $id)
    {
        $expense = $this->service->update($id, $request->validated());

        return $this->getSuccess(
            new ExpenseResource($expense),
            'Expense updated'
        );
    }

    // DELETE /api/v1/expenses/{id}
    public function destroy(int $id)
    {
        $this->service->delete($id);

        return $this->getSuccess(null, 'Expense deleted');
    }

    // PATCH /api/v1/expenses/{id}/pay
    public function pay(int $id)
    {
        $expense = $this->service->markAsPaid($id);

        return $this->getSuccess(
            new ExpenseResource($expense),
            'Expense marked as paid'
        );
    }
}
