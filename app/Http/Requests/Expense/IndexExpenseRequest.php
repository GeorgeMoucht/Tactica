<?php

namespace App\Http\Requests\Expense;

use Illuminate\Foundation\Http\FormRequest;

class IndexExpenseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'query'       => ['nullable', 'string', 'max:180'],
            'pageSize'    => ['nullable', 'integer', 'min:1', 'max:200'],
            'category_id' => ['nullable', 'integer', 'exists:expense_categories,id'],
            'status'      => ['nullable', 'string', 'in:pending,paid'],
            'date_from'   => ['nullable', 'date'],
            'date_to'     => ['nullable', 'date'],
            'sort_by'     => ['nullable', 'string', 'in:id,description,amount,date,status,created_at'],
            'sort_order'  => ['nullable', 'string', 'in:asc,desc'],
        ];
    }
}
