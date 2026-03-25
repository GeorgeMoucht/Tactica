<?php

namespace App\Http\Requests\Expense;

use Illuminate\Foundation\Http\FormRequest;

class UpdateExpenseCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $id = $this->route('id');

        return [
            'name' => ['required', 'string', 'max:150', 'unique:expense_categories,name,' . $id],
        ];
    }
}
