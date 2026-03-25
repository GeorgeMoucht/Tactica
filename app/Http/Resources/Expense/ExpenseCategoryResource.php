<?php

namespace App\Http\Resources\Expense;

use Illuminate\Http\Resources\Json\JsonResource;

class ExpenseCategoryResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id'   => $this->id,
            'name' => $this->name,
        ];
    }
}
