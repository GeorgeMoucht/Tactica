<?php

namespace App\Http\Resources\Expense;

use Illuminate\Http\Resources\Json\JsonResource;

class ExpenseResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id'                  => $this->id,
            'description'         => $this->description,
            'amount'              => (float) $this->amount,
            'date'                => $this->date?->toDateString(),
            'status'              => $this->status,
            'paid_at'             => $this->paid_at?->toDateTimeString(),
            'notes'               => $this->notes,
            'expense_category_id' => $this->expense_category_id,
            'category'            => $this->whenLoaded('category', function () {
                return [
                    'id'   => $this->category->id,
                    'name' => $this->category->name,
                ];
            }),
            'created_at'          => $this->created_at?->toDateTimeString(),
            'updated_at'          => $this->updated_at?->toDateTimeString(),
        ];
    }
}
