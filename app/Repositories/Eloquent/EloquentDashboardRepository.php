<?php

namespace App\Repositories\Eloquent;

use App\Models\CourseClass;
use App\Models\MonthlyDue;
use App\Models\Student;
use App\Repositories\Contracts\DashboardRepository;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class EloquentDashboardRepository implements DashboardRepository
{
    public function outstandingAmount(): float
    {
        return (float) MonthlyDue::pending()->sum('amount');
    }

    public function outstandingStudentsCount(): int
    {
        return (int) MonthlyDue::pending()->distinct('student_id')->count('student_id');
    }

    public function activeClassesWithEnrolled(string $type): Collection
    {
        return CourseClass::where('active', true)
            ->where('type', $type)
            ->withCount(['activeEnrollments as enrolled'])
            ->get();
    }

    public function activeWorkshopsWithEnrolled(): Collection
    {
        $today = Carbon::today();

        return CourseClass::where('active', true)
            ->where('type', 'workshop')
            ->whereHas('sessions', fn ($q) => $q->where('date', '>=', $today))
            ->withCount(['activeEnrollments as enrolled'])
            ->get();
    }

    public function recentStudents(int $limit = 5): Collection
    {
        return Student::with('guardians')
            ->latest()
            ->take($limit)
            ->get();
    }
}
