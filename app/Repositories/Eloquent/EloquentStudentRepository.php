<?php

namespace App\Repositories\Eloquent;

use App\Data\DTO\Registration\StudentDTO;
use App\Models\Student;
use App\Repositories\Contracts\StudentRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class EloquentStudentRepository implements StudentRepository
{
    // Probably will be deprecated.
    public function create(StudentDTO $dto): Student
    {
        return Student::create([
            'first_name'    => $dto->first_name,
            'last_name'     => $dto->last_name,
            'birthdate'     => $dto->birthdate,
            'email'         => $dto->email, 
            'phone'         => $dto->phone,
            'address'       => $dto->address,
            'level'         => $dto->level,
            'interests'     => $dto->interests,
            'notes'         => $dto->notes,
            'medical_note'  => $dto->medical_note,
            'consent_media' => $dto->consent_media
        ]);
    }

    public function createFromArray(array $data): Student
    {
        // $data comes from StoreStudentRequest validated shape
        return Student::create([
            'first_name'        => $data['first_name'],
            'last_name'         => $data['last_name'],
            'birthdate'         => $data['birthdate'],

            'email'             => $data['email']         ?? null, 
            'phone'             => $data['phone']         ?? null,
            'preferred_contact' => $data['preferred_contact'] ?? null,
            'contact_notes'     => $data['contact_notes'] ?? null,

            'address'           => $data['address']       ?? null,   // JSON cast
            'level'             => $data['level']         ?? null,
            'interests'         => $data['interests']     ?? null,   // JSON cast

            'notes'             => $data['notes']         ?? null,
            'medical_note'      => $data['medical_note']  ?? null,
            'consent_media'     => (bool)($data['consent_media'] ?? false),

            'is_member'         => (bool)($data['is_member'] ?? false),
            'registration_date' => $data['registration_date'] ?? null,
        ]);
    }

    public function paginateForList(array $filters = []): LengthAwarePaginator
    {
        $q       = $filters['query'] ?? null;
        $perPage = (int)($filters['perPage'] ?? 10);

        return Student::query()
            ->select(['id','first_name','last_name','email','phone','level','created_at'])
            ->when($q, function ($query) use ($q) {
                $query->where(function ($s) use ($q) {
                    $s->where('first_name', 'like', "%$q%")
                      ->orWhere('last_name',  'like', "%$q%")
                      ->orWhere('email',      'like', "%$q%")
                      ->orWhere('phone',      'like', "%$q%");
                });
            })
            ->latest()
            ->paginate($perPage);
    }

    public function findWithGuardians(int $id): ?Student
    {
        return Student::with([
            'guardians:id,first_name,last_name,email,phone,preferred_contact,address'
        ])->find($id);
    }

    public function findWithMembership(int $id): ?Student
    {
        return Student::with([
            'entitlements.product'
        ])->find($id);
    }

    public function update(int $id, array $data): ?Student
    {
        $student = Student::query()->find($id);

        if (!$student) return null;

        $student->fill([
            'first_name'        => $data['first_name']    ?? $student->first_name,
            'last_name'         => $data['last_name']     ?? $student->last_name,
            'birthdate'         => $data['birthdate']     ?? $student->birthdate,
            'email'             => $data['email']         ?? $student->email,
            'phone'             => $data['phone']         ?? $student->phone,
            'preferred_contact' => $data['preferred_contact'] ?? $student->preferred_contact,
            'contact_notes'     => $data['contact_notes']     ?? $student->contact_notes,
            'address'           => $data['address']       ?? $student->address,
            'level'             => $data['level']         ?? $student->level,
            'interests'         => $data['interests']     ?? $student->interests,
            'notes'             => $data['notes']         ?? $student->notes,
            'medical_note'      => $data['medical_note']  ?? $student->medical_note,
            'consent_media'     => array_key_exists('consent_media', $data) ? $data['consent_media'] : $student->consent_media,
            'is_member'         => array_key_exists('is_member', $data) ? (bool)$data['is_member'] : $student->is_member,
            'registration_date' => array_key_exists('registration_date', $data) ? $data['registration_date'] : $student->registration_date,
        ]);

        if (
            $student->is_member &&
            array_key_exists('is_member', $data) &&
            !array_key_exists('registration_date', $data)
        ) {
            $student->registration_date = now()->toDateString();
        }

        if (!$student->is_member) {
            $student->registration_date = null;
        }

        $student->save();

        return $this->findWithGuardians($student->id);
    }
}