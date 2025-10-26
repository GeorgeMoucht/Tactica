<?php

namespace App\Http\Resources\Student;

use Illuminate\Http\Resources\Json\JsonResource;

class StudentDetailResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id'            => $this->id,
            'first_name'    => $this->first_name,
            'last_name'     => $this->last_name,
            'birthdate'     => $this->birthdate?->toDateString(),
            'email'         => $this->email,
            'phone'         => $this->phone,
            'level'         => $this->level,
            'interests'     => $this->interests ?? [],
            'notes'         => $this->notes,
            'medical_note'  => $this->medical_note,
            'consent_media' => (bool)$this->consent_media,
            'address'       => $this->address,

            'guardians'    => $this->guardians->map(fn($g) => [
                'id'         => $g->id,
                'name'       => trim($g->first_name.' '.$g->last_name),
                'email'      => $g->email,
                'phone'      => $g->phone,
                'preferred_contact' => $g->preferred_contact,
                'address'    => $g->address,
            ]),
        ];
    }
}