<?php

namespace App\Services\Class;

use App\Exceptions\BusinessException;
use App\Exceptions\ConflictException;
use App\Exceptions\NotFoundException;
use App\Models\CourseClass;
use App\Repositories\Contracts\ClassRepository;
use App\Repositories\Contracts\UserRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

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

    public function detail(int $classId): CourseClass
    {
        $class = $this->classes->findDetail($classId);
        if (!$class) {
            throw new NotFoundException('Class not found.');
        }
        return $class;
    }

    public function create(array $data): CourseClass
    {
        $teacherId = !empty($data['teacher_id']) ? (int)$data['teacher_id'] : null;

        if ($teacherId) {
            $this->validateTeacher($teacherId);
        }

        $type = $data['type'] ?? 'weekly';

        if ($type === 'weekly') {
            // conflict check if schedule + teacher exist
            if ($teacherId && !empty($data['day_of_week']) && !empty($data['starts_time']) && !empty($data['ends_time'])) {
                if ($this->classes->teacherHasConflict(
                    $teacherId,
                    (int)$data['day_of_week'],
                    $data['starts_time'],
                    $data['ends_time'],
                    null
                )) {
                    throw new ConflictException('Teacher already has a class that overlaps this time.');
                }
            }
        } elseif ($type === 'workshop') {
            if ($teacherId && !empty($data['sessions'])) {
                if ($this->classes->teacherHasWorkshopConflict($teacherId, $data['sessions'], null)) {
                    throw new ConflictException('Teacher already has a session that overlaps with one of the workshop dates.');
                }
            }
        }

        return DB::transaction(fn() => $this->classes->createFromArray($data));
    }

    public function update(int $classId, array $data): CourseClass
    {
        $existing = $this->classes->findDetail($classId);
        if (!$existing) {
            throw new NotFoundException('Class not found.');
        }

        $teacherId = array_key_exists('teacher_id', $data)
            ? (empty($data['teacher_id']) ? null : (int)$data['teacher_id'])
            : $existing->teacher_id;

        // validate teacher if teacherId present or changed
        if ($teacherId) {
            $this->validateTeacher($teacherId);
        }

        $type = $data['type'] ?? $existing->type;

        if ($type === 'weekly') {
            $dayOfWeek  = array_key_exists('day_of_week', $data) ? $data['day_of_week'] : $existing->day_of_week;
            $startsTime = array_key_exists('starts_time', $data) ? $data['starts_time'] : $existing->starts_time;
            $endsTime   = array_key_exists('ends_time', $data) ? $data['ends_time'] : $existing->ends_time;

            if ($teacherId && $dayOfWeek && $startsTime && $endsTime) {
                if ($this->classes->teacherHasConflict(
                    $teacherId,
                    (int)$dayOfWeek,
                    $startsTime,
                    $endsTime,
                    $classId
                )) {
                    throw new ConflictException('Teacher already has a class that overlaps this time.');
                }
            }
        } elseif ($type === 'workshop') {
            $sessions = $data['sessions'] ?? null;
            if ($teacherId && $sessions) {
                if ($this->classes->teacherHasWorkshopConflict($teacherId, $sessions, $classId)) {
                    throw new ConflictException('Teacher already has a session that overlaps with one of the workshop dates.');
                }
            }
        }

        return DB::transaction(fn() => $this->classes->update($classId, $data));
    }

    public function toggleActive(int $classId): CourseClass
    {
        $class = $this->classes->toggleActive($classId);
        if (!$class) {
            throw new NotFoundException('Class not found.');
        }
        return $class;
    }

    public function destroy(int $classId): bool
    {
        $existing = $this->classes->findDetail($classId);
        if (!$existing) {
            throw new NotFoundException('Class not found.');
        }

        return $this->classes->delete($classId);
    }

    private function validateTeacher(int $teacherId): void
    {
        $teacher = $this->users->findById($teacherId);

        if (!$teacher) {
            throw new NotFoundException('Teacher not found.');
        }

        if (!in_array($teacher->role, ['teacher', 'admin'], true)) {
            throw new BusinessException('Selected user is not a teacher.');
        }
    }
}
