<?php

namespace App\Repositories\Eloquent;

use App\Models\ClassEnrollment;
use App\Repositories\Contracts\EnrollmentRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class EloquentEnrollmentRepository implements EnrollmentRepository
{
    public function findById(int $id): ?ClassEnrollment
    {
        return ClassEnrollment::with(['student', 'courseClass'])->find($id);
    }

    public function isStudentEnrolled(int $studentId, int $classId): bool
    {
        return ClassEnrollment::where('student_id', $studentId)
            ->where('class_id', $classId)
            ->where('status', 'active')
            ->exists();
    }

    public function countActiveEnrollments(int $classId): int
    {
        return ClassEnrollment::where('class_id', $classId)
            ->where('status', 'active')
            ->count();
    }

    public function create(array $data): ClassEnrollment
    {
        return ClassEnrollment::create([
            'student_id'       => $data['student_id'],
            'class_id'         => $data['class_id'],
            'status'           => $data['status'] ?? 'active',
            'enrolled_at'      => $data['enrolled_at'] ?? now()->toDateString(),
            'withdrawn_at'     => $data['withdrawn_at'] ?? null,
            'notes'            => $data['notes'] ?? null,
            'discount_percent' => $data['discount_percent'] ?? 0,
            'discount_amount'  => $data['discount_amount'] ?? 0,
            'discount_note'    => $data['discount_note'] ?? null,
        ]);
    }

    public function update(int $id, array $data): ?ClassEnrollment
    {
        $enrollment = ClassEnrollment::find($id);

        if (!$enrollment) {
            return null;
        }

        $enrollment->fill($data);
        $enrollment->save();

        return $enrollment->load(['student', 'courseClass']);
    }

    public function updateStatus(int $id, string $status, ?string $withdrawnAt = null): ?ClassEnrollment
    {
        $enrollment = ClassEnrollment::find($id);

        if (!$enrollment) {
            return null;
        }

        $enrollment->status = $status;

        if ($status === 'withdrawn' && $withdrawnAt) {
            $enrollment->withdrawn_at = $withdrawnAt;
        }

        $enrollment->save();

        return $enrollment->load(['student', 'courseClass']);
    }

    public function findByStudent(int $studentId): Collection
    {
        return ClassEnrollment::with(['courseClass', 'courseClass.teacher'])
            ->where('student_id', $studentId)
            ->orderByDesc('enrolled_at')
            ->get();
    }

    public function paginateByClass(int $classId, array $filters = []): LengthAwarePaginator
    {
        $perPage = (int) ($filters['perPage'] ?? 10);
        $status  = $filters['status'] ?? null;

        return ClassEnrollment::with(['student.guardians'])
            ->where('class_id', $classId)
            ->when($status, fn ($q) => $q->where('status', $status))
            ->orderByDesc('enrolled_at')
            ->paginate($perPage);
    }
}
