<?php

namespace App\Http\Requests\Expense;

use Illuminate\Foundation\Http\FormRequest;

class UpdateExpenseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'description'         => ['sometimes', 'string', 'max:255'],
            'amount'              => ['sometimes', 'numeric', 'min:0.01'],
            'expense_category_id' => ['nullable', 'integer', 'exists:expense_categories,id'],
            'date'                => ['sometimes', 'date'],
            'status'              => ['sometimes', 'string', 'in:pending,paid'],
            'notes'               => ['nullable', 'string'],
        ];
    }
}
