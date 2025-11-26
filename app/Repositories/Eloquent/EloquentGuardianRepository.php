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

    /**
     * Paginated ist for the guardians index screen.
     */
    public function paginateForList(array $filters = []): LengthAwarePaginator
    {
        $q       = $filters['query'] ?? null;
        $perPage = (int)($filters['perPage'] ?? 10);
        
        return Guardian::query()
            ->select(['id', 'first_name', 'last_name', 'email', 'phone', 'created_at'])
            ->when($q, function ($query) use ($q) {
                $query->where(function ($s) use ($q) {
                    $s->where('first_name', 'like', "%{$q}%")
                      ->orWhere('last_name',  'like', "%{$q}%")
                      ->orWhere('email',      'like', "%{$q}%")
                      ->orWhere('phone',      'like', "%{$q}%");
                });
            })
            ->latest()
            ->paginate($perPage);
    }

    /**
     * Guardian + their students (for the detail dialog).
     */
    public function findWithStudents(int $id): ?Guardian
    {
        return Guardian::with([
            'students:id,first_name,last_name,email,phone'
        ])->find($id);
    }

    /**
     * Update guardian and return it with students. 
     */
    public function update(int $id, array $data): ?Guardian
    {
        $guardian = Guardian::query()->find($id);

        if (!$guardian) {
            return null;
        }

        $guardian->fill([
            'first_name'         => $data['first_name']        ?? $guardian->first_name,
            'last_name'          => $data['last_name']         ?? $guardian->last_name,
            'email'              => $data['email']             ?? $guardian->email,
            'phone'              => $data['phone']             ?? $guardian->phone,
            'address'            => $data['address']           ?? $guardian->address,
            'preferred_contact'  => $data['preferred_contact'] ?? $guardian->preferred_contact,
            'notes'              => $data['notes']             ?? $guardian->notes,
            'newsletter_consent' => array_key_exists('newsletter_consent', $data)
                ? (bool)$data['newsletter_consent']
                : $guardian->newsletter_consent,
        ]);

        $guardian->save();

        return $this->findWithStudents($guardian->id);
    }
}