<?php

namespace App\Http\Resources\Guardian;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class GuardianDetailResource extends JsonResource
{
    /**
     * @return array<string,mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'                 => $this->id,
            'first_name'         => $this->first_name,
            'last_name'          => $this->last_name,
            'name'               => trim(($this->first_name ?? '') . ' ' . ($this->last_name ?? '')),
            'email'              => $this->email,
            'phone'              => $this->phone,
            'address'            => $this->address, // JSON cast on model
            'preferred_contact'  => $this->preferred_contact,
            'notes'              => $this->notes,
            'newsletter_consent' => (bool) $this->newsletter_consent,

            'students' => $this->whenLoaded('students', function () {
                return $this->students->map(function ($s) {
                    return [
                        'id'    => $s->id,
                        'name'  => trim(($s->first_name ?? '') . ' ' . ($s->last_name ?? '')),
                        'email' => $s->email,
                        'phone' => $s->phone,
                        'relation' => $s->pivot->relation ?? null,
                    ];
                })->values();
            }),

            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}