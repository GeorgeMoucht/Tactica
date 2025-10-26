<?php

namespace App\Services\Registration;

use App\Data\DTO\Registration\CreateRegistrationDTO;
use App\Repositories\Contracts\GuardianRepository;
use App\Repositories\Contracts\StudentRepository;
use Illuminate\Support\Facades\DB;

class RegistrationService
{
    public function __construct(
        private GuardianRepository $guardians,
        private StudentRepository $students,
    ) {}

    /** @return array{0:int,1:int[]} [guardianId, studentIds] */
    public function create(CreateRegistrationDTO $dto): array
    {
        return DB::transaction(function () use ($dto) {
            $guardian = $this->guardians->create($dto->guardian);

            $ids = [];
            foreach ($dto->students as $studentDTO) {
                $student = $this->students->create($studentDTO);
                $guardian->students()->attach($student->id);
                $ids[] = $student->id;
            }
            return [$guardian->id, $ids];
        });
    }
}