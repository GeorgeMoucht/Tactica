<?php

namespace App\Services\Dashboard;

use App\Models\CourseClass;
use App\Models\Student;
use App\Repositories\Contracts\DashboardRepository;

class DashboardService
{
    public function __construct(
        private readonly DashboardRepository $repo
    ) {}

    public function widgets(): array
    {
        return [
            'outstanding'          => $this->outstanding(),
            'class_capacity'       => $this->classCapacity(),
            'workshop_capacity'    => $this->workshopCapacity(),
            'recent_registrations' => $this->recentRegistrations(),
        ];
    }

    private function outstanding(): array
    {
        return [
            'total_amount'   => $this->repo->outstandingAmount(),
            'students_count' => $this->repo->outstandingStudentsCount(),
        ];
    }

    private function classCapacity(): array
    {
        return $this->repo->activeClassesWithEnrolled('weekly')
            ->map(fn (CourseClass $c) => [
                'id'         => $c->id,
                'title'      => $c->title,
                'capacity'   => $c->capacity ?? 0,
                'enrolled'   => $c->enrolled,
                'percentage' => $c->capacity
                    ? (int) round(($c->enrolled / $c->capacity) * 100)
                    : 0,
            ])
            ->toArray();
    }

    private function workshopCapacity(): array
    {
        return $this->repo->activeWorkshopsWithEnrolled()
            ->map(fn (CourseClass $c) => [
                'id'         => $c->id,
                'title'      => $c->title,
                'capacity'   => $c->capacity ?? 0,
                'enrolled'   => $c->enrolled,
                'percentage' => $c->capacity
                    ? (int) round(($c->enrolled / $c->capacity) * 100)
                    : 0,
            ])
            ->toArray();
    }

    private function recentRegistrations(): array
    {
        return $this->repo->recentStudents(5)
            ->map(fn (Student $s) => [
                'id'              => $s->id,
                'created_at'      => $s->created_at->toDateString(),
                'student_name'    => $s->name,
                'guardian_name'   => $s->guardians->first()?->name ?? '—',
                'student_summary' => $this->studentSummary($s),
            ])
            ->toArray();
    }

    private function studentSummary(Student $student): string
    {
        $guardian = $student->guardians->first();

        if (! $guardian) {
            return $student->name;
        }

        $siblingCount = $guardian->students()->count();

        return $siblingCount === 1
            ? '1 μαθητής'
            : "{$siblingCount} μαθητές";
    }
}
