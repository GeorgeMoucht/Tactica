<?php

namespace App\Repositories\Eloquent;

use App\Data\DTO\Registration\GuardianDTO;
use App\Models\Guardian;
use App\Repositories\Contracts\GuardianRepository;
use Illuminate\Pagination\LengthAwarePaginator;

class EloquentGuardianRepository implements GuardianRepository
{
    // Probably deprecated
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

    public function createFromArray(array $data): Guardian
    {
        return Guardian::create([
            'first_name'         => $data['first_name'],
            'last_name'          => $data['last_name'],
            'email'              => $data['email']              ?? null,
            'phone'              => $data['phone']              ?? null,
            'address'            => $data['address']            ?? null, // JSON cast
            'preferred_contact'  => $data['preferred_contact']  ?? null,
            'notes'              => $data['notes']              ?? null,
            'newsletter_consent' => (bool)($data['newsletter_consent'] ?? false),
        ]);
    }

    public function findByEmailOrPhone(?string $email, ?string $phone): ?Guardian
    {
        if (!$email && !$phone) {
            return null;
        }

        return Guardian::query()
            ->when($email, fn($q) => $q->where('email', $email))
            ->when($phone, fn($q) => $q->orWhere('phone', $phone))
            ->first();
    }
}