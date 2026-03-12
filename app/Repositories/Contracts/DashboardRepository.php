<?php

namespace App\Repositories\Contracts;

use Illuminate\Support\Collection;

interface DashboardRepository
{
    public function outstandingAmount(): float;

    public function outstandingStudentsCount(): int;

    /**
     * Active classes of a given type with enrolled count.
     *
     * @return Collection<int, \App\Models\CourseClass>
     */
    public function activeClassesWithEnrolled(string $type): Collection;

    /**
     * Active workshops that have future sessions, with enrolled count.
     *
     * @return Collection<int, \App\Models\CourseClass>
     */
    public function activeWorkshopsWithEnrolled(): Collection;

    /**
     * Most recent students with their guardians.
     *
     * @return Collection<int, \App\Models\Student>
     */
    public function recentStudents(int $limit = 5): Collection;
}
