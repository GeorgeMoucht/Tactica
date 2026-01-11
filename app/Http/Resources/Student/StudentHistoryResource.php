<?php

namespace App\Http\Resources\Student;

use Illuminate\Http\Resources\Json\JsonResource;
use Carbon\Carbon;

class StudentHistoryResource extends JsonResource
{
    public function toArray($request)
    {
        $today = Carbon::today();

        return [
            'student_id' => $this['student_id'],
            
            'memberships' => $this['memberships']->map(fn ($e) => [
                'starts_at' => $e->starts_at->toDateString(),
                'ends_at'   => $e->ends_at->toDateString(),
                'active'    => $e->starts_at->lte($today) && $e->ends_at->gte($today),

                'product' => [
                    'id'    => $e->product->id,
                    'name'  => $e->product->name,
                    'price' => $e->product->price,
                ],
            ]),
        ];
    }
}