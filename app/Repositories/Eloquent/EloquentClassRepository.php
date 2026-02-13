<?php

namespace App\Repositories\Eloquent;

use App\Models\ClassSession;
use App\Models\CourseClass;
use App\Repositories\Contracts\ClassRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class EloquentClassRepository implements ClassRepository
{
    public function paginateForList(array $filters = []): LengthAwarePaginator
    {
        $q          = $filters['query'] ?? null;
        $perPage    = (int) ($filters['perPage'] ?? 10);
        $type       = $filters['type'] ?? null;
        $active     = $filters['active'] ?? null;
        $dayOfWeek  = $filters['day_of_week'] ?? null;
        $teacherId  = $filters['teacher_id'] ?? null;
        $sortBy     = $filters['sort_by'] ?? 'id';
        $sortOrder  = $filters['sort_order'] ?? 'desc';

        // Allowed sort fields
        $allowedSortFields = ['id', 'title', 'type', 'active', 'day_of_week', 'capacity', 'created_at'];

        // Handle nested sort fields like 'teacher.name'
        if (str_starts_with($sortBy, 'teacher.')) {
            $sortBy = 'teacher_id'; // Fallback for nested sorting
        }

        if (!in_array($sortBy, $allowedSortFields)) {
            $sortBy = 'id';
        }

        $sortOrder = strtolower($sortOrder) === 'asc' ? 'asc' : 'desc';

        return CourseClass::query()
            ->with(['teacher:id,name,role', 'sessions'])
            ->when($q, function ($query) use ($q) {
                $query->where(function ($x) use ($q) {
                    $x->where('title', 'like', "%$q%")
                      ->orWhere('description', 'like', "%$q%");
                });
            })
            ->when($type, fn($query) => $query->where('type', $type))
            ->when($active !== null, fn($query) => $query->where('active', $active))
            ->when($dayOfWeek !== null, fn($query) => $query->where('day_of_week', $dayOfWeek))
            ->when($teacherId !== null, fn($query) => $query->where('teacher_id', $teacherId))
            ->orderBy($sortBy, $sortOrder)
            ->paginate($perPage);
    }

    public function findDetail(int $id): ?CourseClass
    {
        return CourseClass::query()
            ->with(['teacher:id,name,role', 'sessions'])
            ->find($id);
    }

    public function createFromArray(array $data): CourseClass
    {
        $class = CourseClass::create([
            'title'         => $data['title'],
            'description'   => $data['description'] ?? null,
            'type'          => $data['type'] ?? 'weekly',
            'active'        => $data['active'] ?? true,

            'day_of_week'   => $data['day_of_week'] ?? null,
            'starts_time'   => $data['starts_time'] ?? null,
            'ends_time'     => $data['ends_time'] ?? null,

            'capacity'      => $data['capacity'] ?? null,
            'monthly_price' => $data['monthly_price'] ?? 40.00,
            'teacher_id'    => $data['teacher_id'] ?? null,
        ]);

        if (($data['type'] ?? 'weekly') === 'workshop' && !empty($data['sessions'])) {
            $class->sessions()->createMany($data['sessions']);
        }

        return $this->findDetail($class->id);
    }

    public function update(int $id, array $data): ?CourseClass
    {
        $class = CourseClass::query()->find($id);

        if (!$class) return null;

        $class->fill([
            'title'       => $data['title']       ?? $class->title,
            'description' => array_key_exists('description', $data) ? ($data['description'] ?? null) : $class->description,
            'type'        => $data['type']         ?? $class->type,
            'active'      => array_key_exists('active', $data) ? $data['active'] : $class->active,

            'day_of_week' => array_key_exists('day_of_week', $data) ? ($data['day_of_week'] ?? null) : $class->day_of_week,
            'starts_time' => array_key_exists('starts_time', $data) ? ($data['starts_time'] ?? null) : $class->starts_time,
            'ends_time'   => array_key_exists('ends_time', $data) ? ($data['ends_time'] ?? null) : $class->ends_time,

            'capacity'      => array_key_exists('capacity', $data) ? ($data['capacity'] ?? null) : $class->capacity,
            'monthly_price' => array_key_exists('monthly_price', $data) ? $data['monthly_price'] : $class->monthly_price,
            'teacher_id'    => array_key_exists('teacher_id', $data) ? ($data['teacher_id'] ?? null) : $class->teacher_id,
        ]);

        $class->save();

        // Sync workshop sessions if provided
        if (array_key_exists('sessions', $data)) {
            $class->sessions()->delete();
            if (!empty($data['sessions'])) {
                $class->sessions()->createMany($data['sessions']);
            }
        }

        return $this->findDetail($class->id);
    }

    public function delete(int $id): bool
    {
        $class = CourseClass::query()->find($id);
        if (!$class) return false;

        $class->delete();
        return true;
    }

    public function toggleActive(int $id): ?CourseClass
    {
        $class = CourseClass::query()->find($id);
        if (!$class) return null;

        $class->active = !$class->active;
        $class->save();

        return $this->findDetail($class->id);
    }

    public function teacherHasConflict(int $teacherId, int $dayOfWeek, string $startsTime, string $endsTime, ?int $excludeClassId = null): bool
    {
        return CourseClass::query()
            ->where('teacher_id', $teacherId)
            ->where('type', 'weekly')
            ->where('day_of_week', $dayOfWeek)
            ->when($excludeClassId, fn($q) => $q->where('id', '!=', $excludeClassId))
            ->where('starts_time', '<', $endsTime)
            ->where('ends_time', '>', $startsTime)
            ->exists();
    }

    public function teacherHasWorkshopConflict(int $teacherId, array $sessions, ?int $excludeClassId = null): bool
    {
        foreach ($sessions as $session) {
            $conflict = ClassSession::query()
                ->whereHas('courseClass', function ($q) use ($teacherId, $excludeClassId) {
                    $q->where('teacher_id', $teacherId);
                    if ($excludeClassId) {
                        $q->where('id', '!=', $excludeClassId);
                    }
                })
                ->where('date', $session['date'])
                ->where('starts_time', '<', $session['ends_time'])
                ->where('ends_time', '>', $session['starts_time'])
                ->exists();

            if ($conflict) return true;
        }

        return false;
    }
}
