<?php

namespace App\Http\Requests\Expense;

use Illuminate\Foundation\Http\FormRequest;

class StoreExpenseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'description'         => ['required', 'string', 'max:255'],
            'amount'              => ['required', 'numeric', 'min:0.01'],
            'expense_category_id' => ['nullable', 'integer', 'exists:expense_categories,id'],
            'date'                => ['required', 'date'],
            'status'              => ['sometimes', 'string', 'in:pending,paid'],
            'notes'               => ['nullable', 'string'],
        ];
    }
}
