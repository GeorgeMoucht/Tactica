<?php

namespace App\Services\Class;

use App\Models\CourseClass;
use App\Repositories\Contracts\ClassRepository;
use App\Repositories\Contracts\UserRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ClassService
{
    private readonly ClassRepository $classes;
    private readonly UserRepository $users;

    public function __construct(ClassRepository $classes, UserRepository $users)
    {
        $this->classes = $classes;
        $this->users = $users;
    }

    public function list(array $filters): LengthAwarePaginator
    {
        return $this->classes->paginateForList($filters);
    }

    public function detail(int $classId): ?CourseClass
    {
        return $this->classes->findDetail($classId);
    }

    public function create(array $data): CourseClass
    {
        $teacherId = !empty($data['teacher_id']) ? (int)$data['teacher_id'] : null;

        if ($teacherId) {
            $teacher = $this->users->findById($teacherId);

            if (!$teacher) {
                throw new \DomainException('Teacher not found.');
            }

            if (!in_array($teacher->role, ['teacher', 'admin'], true)) {
                throw new \DomainException('Selected user is not a teacher.');
            }
        }

        // conflict check if schedule + teacher exist
        if ($teacherId && !empty($data['day_of_week']) && !empty($data['starts_time']) && !empty($data['ends_time'])) {
            $hasConflict = $this->classes->teacherHasConflict(
                $teacherId,
                (int)$data['day_of_week'],
                $data['starts_time'],
                $data['ends_time'],
                null
            );

            if ($hasConflict) {
                throw new \DomainException('Teacher already has a class that overlaps this time.');
            }
        }

        return $this->classes->createFromArray($data);
    }

    public function update(int $classId, array $data): ?CourseClass
    {
        $existing = $this->classes->findDetail($classId);
        if (!$existing) return null;

        $teacherId = array_key_exists('teacher_id', $data)
            ? (empty($data['teacher_id']) ? null : (int)$data['teacher_id'])
            : $existing->teacher_id;

        // validate teacher if teacherId present or changed
        if ($teacherId) {
            $teacher = $this->users->findById($teacherId);

            if (!$teacher) {
                throw new \DomainException('Teacher not found.');
            }

            if (!in_array($teacher->role, ['teacher', 'admin'], true)) {
                throw new \DomainException('Selected user is not a teacher.');
            }
        }

        $dayOfWeek  = array_key_exists('day_of_week', $data) ? $data['day_of_week'] : $existing->day_of_week;
        $startsTime = array_key_exists('starts_time', $data) ? $data['starts_time'] : $existing->starts_time;
        $endsTime   = array_key_exists('ends_time', $data) ? $data['ends_time'] : $existing->ends_time;

        // conflict check only if schedule + teacher are all present
        if ($teacherId && $dayOfWeek && $startsTime && $endsTime) {
            $hasConflict = $this->classes->teacherHasConflict(
                $teacherId,
                (int)$dayOfWeek,
                $startsTime,
                $endsTime,
                $classId
            );

            if ($hasConflict) {
                throw new \DomainException('Teacher already has a class that overlaps this time.');
            }
        }

        return $this->classes->update($classId, $data);
    }
}