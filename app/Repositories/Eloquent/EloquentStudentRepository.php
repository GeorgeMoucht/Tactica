<?php

namespace App\Repositories\Eloquent;

use App\Data\DTO\Registration\StudentDTO;
use App\Models\Student;
use App\Repositories\Contracts\StudentRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class EloquentStudentRepository implements StudentRepository
{
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

    public function update(int $id, array $data): ?Student
    {
        $student = Student::query()->find($id);

        if (!$student) return null;

        $student->fill([
            'first_name'    => $data['first_name']    ?? $student->first_name,
            'last_name'     => $data['last_name']     ?? $student->last_name,
            'birthdate'     => $data['birthdate']     ?? $student->birthdate,
            'email'         => $data['email']         ?? $student->email,
            'phone'         => $data['phone']         ?? $student->phone,
            'address'       => $data['address']       ?? $student->address,
            'level'         => $data['level']         ?? $student->level,
            'interests'     => $data['interests']     ?? $student->interests,
            'notes'         => $data['notes']         ?? $student->notes,
            'medical_note'  => $data['medical_note']  ?? $student->medical_note,
            'consent_media' => array_key_exists('consent_media', $data) ? $data['consent_media'] : $student->consent_media,
        ]);

        $student->save();

        return $this->findWithGuardians($student->id);
    }
}