<?php

namespace App\Repositories\Eloquent;

use App\Data\DTO\Registration\GuardianDTO;
use App\Models\Guardian;
use App\Repositories\Contracts\GuardianRepository;
use Illuminate\Pagination\LengthAwarePaginator;

class EloquentGuardianRepository implements GuardianRepository
{
    public function create(GuardianDTO $dto): Guardian
    {
        return Guardian::create([
            'first_name'            => $dto->first_name,
            'last_name'             => $dto->last_name,
            'email'                 => $dto->email,
            'phone'                 => $dto->phone,
            'address'               => $dto->address,
            'preferred_contact'     => $dto->preferred_contact,
            'notes'                 => $dto->notes,
            'newsletter_consent'    => $dto->newsletter_consent
        ]);
    }
}