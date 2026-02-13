<?php

namespace App\Http\Resources\Class;

use Illuminate\Http\Resources\Json\JsonResource;

class ClassDetailResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id'          => $this->id,
            'title'       => $this->title,
            'description' => $this->description,
            'type'        => $this->type,
            'active'      => (bool) $this->active,

            'day_of_week' => $this->day_of_week,
            'starts_time' => $this->starts_time,
            'ends_time'   => $this->ends_time,

            'capacity'      => $this->capacity,
            'monthly_price' => (float) $this->monthly_price,

            'teacher' => $this->teacher ? [
                'id'   => $this->teacher->id,
                'name' => $this->teacher->name ?? null,
                'role' => $this->teacher->role ?? null,
            ] : null,

            'sessions' => $this->whenLoaded('sessions', function () {
                return $this->sessions->map(fn($s) => [
                    'id'          => $s->id,
                    'date'        => $s->date->toDateString(),
                    'starts_time' => $s->starts_time,
                    'ends_time'   => $s->ends_time,
                ]);
            }, []),

            'created_at'  => $this->created_at?->toDateTimeString(),
            'updated_at'  => $this->updated_at?->toDateTimeString(),
        ];
    }
}
