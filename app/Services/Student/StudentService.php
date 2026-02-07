<?php

namespace App\Services\Student;

use App\Exceptions\NotFoundException;
use App\Http\Requests\Student\StoreStudentRequest;
use App\Models\Student;
use App\Repositories\Contracts\GuardianRepository;
use App\Repositories\Contracts\StudentRepository;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Contracts\Pagination\LengthAwarePaginator as LengthAwarePaginatorContract;
use Illuminate\Pagination\LengthAwarePaginator;

class StudentService
{
    public function __construct(
        private StudentRepository $students,
        private GuardianRepository $guardians
    )
    {
        
    }

    /**
     * Returns a paginator of student list rows.
     * Structure per row: id, name, email, phone, level, created_at
     * 
     * @param array{q?:string|null, perPage?:int|null} $filters
     */
    public function list(array $filters = []): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        $p = $this->students->paginateForList($filters);

        $items = collect($p->items())->map(function (\App\Models\Student $s) {
            return [
                'id'         => $s->id,
                'name'       => trim($s->first_name.' '.$s->last_name),
                'email'      => $s->email,
                'phone'      => $s->phone,
                'is_member'  => (bool) $s->is_member,
                'level'      => $s->level,
                'created_at' => $s->created_at?->toIso8601String(), // <-- fix the typo
            ];
        })->values();

        return new LengthAwarePaginator(
            $items,
            $p->total(),
            $p->perPage(),
            $p->currentPage(),
            [
                'path'  => request()->url(),
                'query' => request()->query(),
            ]
            );
    }

    /**
     * Student detail with guardians
     */
    public function detail(int $id): Student
    {
        $student = $this->students->findWithGuardians($id);
        if (!$student) {
            throw new NotFoundException('Student not found.');
        }
        return $student;
    }

    public function update(int $id, array $data): Student
    {
        $student = $this->students->update($id, $data);
        if (!$student) {
            throw new NotFoundException('Student not found.');
        }
        return $student;
    }

    /**
     * Create student with guardians in one go.
     * Returns [studentId, guardianIds].
     * 
     * @param array{student: array, guardians?: array[]} $data
     * @return array{0:int,1:int[]}
     */
    public function createWithGuardians(array $data): array
    {
        return DB::transaction(function () use ($data) {
            $s      = $data['student'];
            $gList  = $data['guardians'] ?? [];

            // If minor and student has no address, inherit first guardian's address if available
            $isMinor = Carbon::parse($s['birthdate'])->diffInYears(Carbon::today()) < 18;
            $studentHasAddress = is_array($s['address'] ?? null) && array_filter($s['address']);
            if ($isMinor && !$studentHasAddress && !empty($gList[0]['address'])) {
                $s['address'] = $gList[0]['address'];
            }

            // Create student
            $student = $this->students->createFromArray($s);

            // Attach guardians (dedupe by email/phone if possible)
            $guardianIds = [];
            foreach ($gList as $g) {
                $existing = $this->guardians->findByEmailOrPhone($g['email'] ?? null, $g['phone'] ?? null);
                $guardian = $existing ?? $this->guardians->createFromArray($g);
                $guardianIds[] = $guardian->id;

                // Pivot attach
                $guardian->students()->syncWithoutDetaching([
                    $student->id => ['relation' => $g['relation'] ?? null]
                ]);
            }

            return [$student->id, $guardianIds];
        });
    }
}