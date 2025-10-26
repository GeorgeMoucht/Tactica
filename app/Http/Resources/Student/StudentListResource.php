<?php

namespace App\Http\Resources\Student;

use Illuminate\Http\Resources\Json\JsonResource;

class StudentListResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id'            => $this->id,
            'name'          => trim($this->first_name. ' '.$this->last_name),
            'email'         => $this->email,
            'phone'         => $this->phone,
            'level'         => $this->level,
            'created_at'    => $this->created_at?->toIso8601String(),
        ];
    }
}