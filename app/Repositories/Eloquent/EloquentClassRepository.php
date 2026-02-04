<?php

namespace App\Repositories\Eloquent;

use App\Models\CourseClass;
use App\Repositories\Contracts\ClassRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class EloquentClassRepository implements ClassRepository
{
    public function paginateForList(array $filters = []): LengthAwarePaginator
    {
        $q       = $filters['query'] ?? null;
        $perPage = (int) ($filters['perPage'] ?? 10);

        return CourseClass::query()
            ->with(['teacher:id,name,role'])
            ->when($q, function ($query) use ($q) {
                $query->where(function ($x) use ($q) {
                    $x->where('title', 'like', "%$q%")
                      ->orWhere('description', 'like', "%$q%");
                });
            })
            ->orderByDesc('id')
            ->paginate($perPage);
    }

    public function findDetail(int $id): ?CourseClass
    {
        return CourseClass::query()
            ->with(['teacher:id,name,role'])
            ->find($id);
    }

    public function createFromArray(array $data): CourseClass
    {
        return CourseClass::create([
            'title'         => $data['title'],
            'description'   => $data['description'] ?? null,
            
            'day_of_week'   => $data['day_of_week'] ?? null,
            'starts_time'   => $data['starts_time'] ?? null,
            'ends_time'     => $data['ends_time'] ?? null,

            'capacity'      => $data['capacity'] ?? null,
            'teacher_id'    => $data['teacher_id'] ?? null,
        ]);
    }

    public function update(int $id, array $data): ?CourseClass
    {
        $class = CourseClass::query()->find($id);

        if (!$class) return null;

        $class->fill([
            'title'       => $data['title']       ?? $class->title,
            'description' => array_key_exists('description', $data) ? ($data['description'] ?? null) : $class->description,

            'day_of_week' => array_key_exists('day_of_week', $data) ? ($data['day_of_week'] ?? null) : $class->day_of_week,
            'starts_time' => array_key_exists('starts_time', $data) ? ($data['starts_time'] ?? null) : $class->starts_time,
            'ends_time'   => array_key_exists('ends_time', $data) ? ($data['ends_time'] ?? null) : $class->ends_time,

            'capacity'    => array_key_exists('capacity', $data) ? ($data['capacity'] ?? null) : $class->capacity,
            'teacher_id'  => array_key_exists('teacher_id', $data) ? ($data['teacher_id'] ?? null) : $class->teacher_id,
        ]);

        $class->save();

        return $this->findDetail($class->id);
    }

    public function teacherHasConflict(int $teacherId, int $dayOfWeek, string $startsTime, string $endsTime, ?int $excludeClassId = null): bool
    {
        return CourseClass::query()
            ->where('teacher_id', $teacherId)
            ->where('day_of_week', $dayOfWeek)
            ->when($excludeClassId, fn($q) => $q->where('id', '!=', $excludeClassId))
            ->where('starts_time', '<', $endsTime)
            ->where('ends_time', '>', $startsTime)
            ->exists();
    }
}