<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\BaseApiController;
use App\Http\Requests\Expense\StoreExpenseCategoryRequest;
use App\Http\Requests\Expense\UpdateExpenseCategoryRequest;
use App\Http\Resources\Expense\ExpenseCategoryResource;
use App\Services\Expense\ExpenseCategoryService;

class ExpenseCategoryController extends BaseApiController
{
    private readonly ExpenseCategoryService $service;

    public function __construct(ExpenseCategoryService $service)
    {
        $this->service = $service;
    }

    // GET /api/v1/expense-categories
    public function index()
    {
        $categories = $this->service->all();

        return $this->getSuccess(
            ExpenseCategoryResource::collection($categories),
            'Expense categories retrieved'
        );
    }

    // POST /api/v1/expense-categories
    public function store(StoreExpenseCategoryRequest $request)
    {
        $category = $this->service->create($request->validated());

        return $this->actionSuccess(
            'Expense category created',
            new ExpenseCategoryResource($category)
        );
    }

    // PUT /api/v1/expense-categories/{id}
    public function update(UpdateExpenseCategoryRequest $request, int $id)
    {
        $category = $this->service->update($id, $request->validated());

        return $this->getSuccess(
            new ExpenseCategoryResource($category),
            'Expense category updated'
        );
    }

    // DELETE /api/v1/expense-categories/{id}
    public function destroy(int $id)
    {
        $this->service->delete($id);

        return $this->getSuccess(null, 'Expense category deleted');
    }
}
